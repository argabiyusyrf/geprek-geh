<?php
class Auth {
    public static function check() {
        return isset($_SESSION['user_id']);
    }

    public static function admin() {
        return self::check() && ($_SESSION['role'] ?? '') === 'admin';
    }

    public static function user() {
        if (!self::check()) return null;
        $db = Database::getInstance();
        return $db->fetchOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
    }

    public static function id() {
        return $_SESSION['user_id'] ?? null;
    }

    public static function requireLogin() {
        if (!self::check()) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Silakan login terlebih dahulu.'];
            header('Location: /geprek-geh/auth/login');
            exit;
        }
    }

    public static function requireAdmin() {
        self::requireLogin();
        if (!self::admin()) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Akses ditolak.'];
            header('Location: /geprek-geh/');
            exit;
        }
    }

    /**
     * Autentikasi tanpa membuat sesi login.
     * Mengembalikan data user bila email+password cocok (tahap-1 dari login 2FA),
     * atau false bila gagal.
     */
    public static function login($email, $password) {
        $db = Database::getInstance();
        $user = $db->fetchOne("SELECT * FROM users WHERE email = ?", [$email]);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    /** Bangun sesi login penuh dari data user (dipakai login biasa & setelah verifikasi 2FA). */
    public static function establishSession(array $user) {
        session_regenerate_id(true);
        unset($_SESSION['twofa_uid'], $_SESSION['twofa_name'], $_SESSION['twofa_role']);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        self::mergeCart(session_id());
    }

    private static function mergeCart($guest_session_id) {
        $db = Database::getInstance();
        $guest_items = $db->fetchAll(
            "SELECT * FROM cart WHERE session_id = ? AND user_id IS NULL",
            [$guest_session_id]
        );
        if (empty($guest_items)) return;

        foreach ($guest_items as $item) {
            $user = $db->fetchOne(
                "SELECT * FROM cart WHERE user_id = ? AND product_id = ?",
                [$_SESSION['user_id'], $item['product_id']]
            );
            if ($user) {
                $db->update('cart', ['quantity' => $user['quantity'] + $item['quantity']], 'id = ?', [$user['id']]);
            } else {
                $db->insert('cart', [
                    'user_id'    => $_SESSION['user_id'],
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                ]);
            }
            $db->delete('cart', 'id = ?', [$item['id']]);
        }
    }

    public static function register($name, $email, $password, $phone = null) {
        $db = Database::getInstance();
        if ($db->fetchOne("SELECT id FROM users WHERE email = ?", [$email])) {
            return false;
        }
        $id = $db->insert('users', [
            'name'     => $name,
            'email'    => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'phone'    => $phone,
            'role'     => 'customer',
        ]);
        $_SESSION['user_id'] = $id;
        $_SESSION['user_name'] = $name;
        $_SESSION['role'] = 'customer';
        return true;
    }

    public static function logout() {
        session_destroy();
        header('Location: /geprek-geh/');
        exit;
    }
}
