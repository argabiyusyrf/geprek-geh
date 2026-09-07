<?php
class PasswordResetController {
    private const TTL_MINUTES = 60;

    public function requestForm() {
        if (Auth::check()) redirect('/geprek-geh/account');
        $page_title = 'Lupa Password';
        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/auth/forgot.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }

    public function request() {
        if (!verify_csrf()) {
            flash_set('error', 'Sesi tidak valid, silakan coba lagi.');
            header('Location: /geprek-geh/auth/forgot'); exit;
        }
        $email = strtolower(trim($_POST['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash_set('error', 'Email tidak valid.');
            header('Location: /geprek-geh/auth/forgot'); exit;
        }

        $db = Database::getInstance();
        $user = $db->fetchOne("SELECT id, name, email FROM users WHERE email = ?", [$email]);

        // Always respond with the same message — never leak whether an email exists
        if ($user) {
            $selector = bin2hex(random_bytes(16));           // public, in URL
            $token    = bin2hex(random_bytes(32));           // secret
            $hash     = hash('sha256', $token);
            $expires  = date('Y-m-d H:i:s', time() + self::TTL_MINUTES * 60);

            // Invalidate prior outstanding tokens for this user
            $db->query("UPDATE password_resets SET used_at = NOW() WHERE user_id = ? AND used_at IS NULL", [$user['id']]);

            $db->insert('password_resets', [
                'user_id'    => $user['id'],
                'selector'   => $selector,
                'token_hash' => $hash,
                'expires_at' => $expires,
            ]);

            $app = require __DIR__ . '/../config/app.php';
            $base = rtrim($app['url'], '/');
            $resetUrl = $base . '/auth/reset?selector=' . urlencode($selector) . '&token=' . urlencode($token);

            try {
                Mail::passwordReset($user['email'], $user['name'], $resetUrl, self::TTL_MINUTES);
            } catch (Exception $e) {
                error_log('[password reset] mail error: ' . $e->getMessage());
            }
        }

        flash_set('success', 'Kalau email terdaftar, link reset sudah dikirim. Cek inbox (atau folder spam) dalam 60 menit.');
        header('Location: /geprek-geh/auth/forgot'); exit;
    }

    public function resetForm() {
        if (Auth::check()) redirect('/geprek-geh/account');
        $selector = $_GET['selector'] ?? '';
        $token    = $_GET['token'] ?? '';
        $page_title = 'Reset Password';
        $valid = self::validateToken($selector, $token);
        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/auth/reset.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }

    public function reset() {
        if (!verify_csrf()) {
            flash_set('error', 'Sesi tidak valid.');
            header('Location: /geprek-geh/auth/login'); exit;
        }
        $selector = $_POST['selector'] ?? '';
        $token    = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['password_confirm'] ?? '';

        if (strlen($password) < 6) {
            flash_set('error', 'Password minimal 6 karakter.');
            header('Location: /geprek-geh/auth/reset?selector=' . urlencode($selector) . '&token=' . urlencode($token)); exit;
        }
        if ($password !== $confirm) {
            flash_set('error', 'Konfirmasi password tidak cocok.');
            header('Location: /geprek-geh/auth/reset?selector=' . urlencode($selector) . '&token=' . urlencode($token)); exit;
        }

        $row = self::validateToken($selector, $token);
        if (!$row) {
            flash_set('error', 'Link reset sudah tidak valid atau kedaluwarsa. Minta link baru.');
            header('Location: /geprek-geh/auth/forgot'); exit;
        }

        $db = Database::getInstance();
        $db->update('users', [
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ], 'id = ?', [$row['user_id']]);
        $db->update('password_resets', ['used_at' => date('Y-m-d H:i:s')], 'id = ?', [$row['id']]);

        flash_set('success', 'Password berhasil diperbarui. Silakan login dengan password baru.');
        header('Location: /geprek-geh/auth/login'); exit;
    }

    /** Returns the matching row if selector+token valid and unused; else false. */
    private static function validateToken(string $selector, string $token) {
        if (strlen($selector) !== 32 || strlen($token) !== 64) return false;
        $db = Database::getInstance();
        $row = $db->fetchOne(
            "SELECT * FROM password_resets WHERE selector = ? AND used_at IS NULL AND expires_at > NOW()",
            [$selector]
        );
        if (!$row) return false;
        $hash = hash('sha256', $token);
        if (!hash_equals($row['token_hash'], $hash)) return false;
        return $row;
    }
}