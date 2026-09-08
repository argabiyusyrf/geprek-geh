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
        self::trackSession($user['id']);
    }

    /** Simpan info sesi perangkat ke database. */
    private static function trackSession(int $userId) {
        $db = Database::getInstance();
        $sid = session_id();
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $parsed = self::parseUserAgent($ua);

        // Tandai semua sesi lain user ini sebagai bukan current
        $db->update('sessions', ['is_current' => 0], 'user_id = ?', [$userId]);

        // Cek apakah sesi ini sudah ada (misal reload)
        $existing = $db->fetchOne(
            "SELECT id FROM sessions WHERE user_id = ? AND session_id = ?",
            [$userId, $sid]
        );

        $data = [
            'user_agent'  => mb_substr($ua, 0, 512),
            'ip_address'  => $ip,
            'device_type' => $parsed['device'],
            'browser'     => $parsed['browser'],
            'os'          => $parsed['os'],
            'is_current'  => 1,
        ];

        if ($existing) {
            $db->update('sessions', $data, 'id = ?', [$existing['id']]);
        } else {
            $data['user_id']    = $userId;
            $data['session_id'] = $sid;
            $db->insert('sessions', $data);
        }
    }

    /** Parse User-Agent string untuk device, browser, OS. */
    private static function parseUserAgent(string $ua): array {
        $device = 'Desktop';
        if (preg_match('/mobile|android|iphone|ipad|ipod/i', $ua)) {
            $device = 'Mobile';
        } elseif (preg_match('/tablet|ipad/i', $ua)) {
            $device = 'Tablet';
        }

        $browser = 'Browser Lain';
        if (preg_match('/Edg(?:e|A|iOS)?\/(\d+)/i', $ua, $m)) {
            $browser = 'Edge ' . $m[1];
        } elseif (preg_match('/Chrome\/(\d+)/i', $ua, $m)) {
            $browser = 'Chrome ' . $m[1];
        } elseif (preg_match('/Firefox\/(\d+)/i', $ua, $m)) {
            $browser = 'Firefox ' . $m[1];
        } elseif (preg_match('/Version\/(\d+).*Safari/i', $ua, $m)) {
            $browser = 'Safari ' . $m[1];
        } elseif (preg_match('/Opera|OPR\/(\d+)/i', $ua, $m)) {
            $browser = 'Opera ' . ($m[1] ?? '');
        }

        $os = 'OS Lain';
        if (preg_match('/Windows NT 10/i', $ua)) {
            $os = 'Windows 10/11';
        } elseif (preg_match('/Windows NT 6\.3/i', $ua)) {
            $os = 'Windows 8.1';
        } elseif (preg_match('/Windows NT 6\.1/i', $ua)) {
            $os = 'Windows 7';
        } elseif (preg_match('/Mac OS X ([\d_]+)/i', $ua, $m)) {
            $os = 'macOS ' . str_replace('_', '.', $m[1]);
        } elseif (preg_match('/Android (\d+[\.\d]*)/i', $ua, $m)) {
            $os = 'Android ' . $m[1];
        } elseif (preg_match('/iPhone OS ([\d_]+)/i', $ua, $m)) {
            $os = 'iOS ' . str_replace('_', '.', $m[1]);
        } elseif (preg_match('/iPad.*OS ([\d_]+)/i', $ua, $m)) {
            $os = 'iPadOS ' . str_replace('_', '.', $m[1]);
        } elseif (preg_match('/Linux/i', $ua)) {
            $os = 'Linux';
        }

        return ['device' => $device, 'browser' => $browser, 'os' => $os];
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
        if (self::check()) {
            $db = Database::getInstance();
            $db->delete('sessions', 'session_id = ?', [session_id()]);
        }
        session_destroy();
        header('Location: /geprek-geh/');
        exit;
    }
}
