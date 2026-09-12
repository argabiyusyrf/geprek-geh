<?php
class PasswordResetController {
    private const TTL_MINUTES = 60;

public function requestForm() {
        if (Auth::check()) redirect('/geprek-geh/account');

        $flow = $_SESSION['forgot_flow'] ?? null;
        $fr_step  = $flow['step'] ?? 'email';
        $fr_email = $flow['email'] ?? '';
        $fr_name  = $flow['name'] ?? '';
        $fr_error = $_SESSION['forgot_error'] ?? '';
        $fr_old   = $_SESSION['forgot_old'] ?? '';
        // Flow "method" dipertahankan untuk langkah berikutnya; error/old dibaca sekali.
        unset($_SESSION['forgot_error'], $_SESSION['forgot_old']);

        $page_title = 'Lupa Password';
        require __DIR__ . '/../views/layouts/auth-header.php';
        require __DIR__ . '/../views/auth/forgot.php';
        require __DIR__ . '/../views/layouts/auth-footer.php';
    }

    public function request() {
        if (!verify_csrf()) {
            flash_set('error', 'Sesi tidak valid, silakan coba lagi.');
            header('Location: /geprek-geh/auth/forgot'); exit;
        }
        $step  = $_POST['step'] ?? 'email';
        $ip    = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $db    = Database::getInstance();

        // ── LANGKAH 1: cek email terdaftar atau tidak ──
        if ($step === 'email') {
            $email = strtolower(trim($_POST['email'] ?? ''));
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['forgot_old'] = $email;
                $_SESSION['forgot_error'] = 'Format email tidak valid.';
                header('Location: /geprek-geh/auth/forgot'); exit;
            }
            if (!RateLimiter::attempt('forgot-check:' . $ip, 15, 300)) {
                $_SESSION['forgot_old'] = $email;
                $_SESSION['forgot_error'] = 'Terlalu banyak percobaan. Coba lagi dalam 5 menit.';
                header('Location: /geprek-geh/auth/forgot'); exit;
            }
            $user = $db->fetchOne("SELECT id, name, email FROM users WHERE email = ?", [$email]);
            if (!$user) {
                $_SESSION['forgot_old'] = $email;
                $_SESSION['forgot_error'] = "Email {$email} belum terdaftar. Silakan daftar dulu.";
                unset($_SESSION['forgot_flow']);
                header('Location: /geprek-geh/auth/forgot'); exit;
            }
            $_SESSION['forgot_flow'] = ['step' => 'method', 'uid' => (int) $user['id'], 'email' => $user['email'], 'name' => $user['name']];
            header('Location: /geprek-geh/auth/forgot'); exit;
        }

        // ── LANGKAH 2: pilih metode (link email / kata kunci) ──
        $flow = $_SESSION['forgot_flow'] ?? null;
        if (!$flow || ($flow['step'] ?? '') !== 'method' || empty($flow['email'])) {
            unset($_SESSION['forgot_flow']);
            flash_set('error', 'Mulai lagi dari email terdaftar.');
            header('Location: /geprek-geh/auth/forgot'); exit;
        }
        $email = $flow['email'];
        $method = $_POST['method'] ?? 'email';

        if ($method === 'keyword') {
            if (!RateLimiter::attempt('keyword:' . $email, 5, 300)) {
                $_SESSION['forgot_error'] = 'Terlalu banyak percobaan. Coba lagi dalam 5 menit.';
                header('Location: /geprek-geh/auth/forgot'); exit;
            }
            if (!RateLimiter::attempt('keyword-ip:' . $ip, 10, 600)) {
                $_SESSION['forgot_error'] = 'Terlalu banyak percobaan dari perangkat ini. Coba lagi nanti.';
                header('Location: /geprek-geh/auth/forgot'); exit;
            }

            $phrase = $_POST['keyword'] ?? '';
            if ($phrase === '' || !Auth::verifyRecoveryKeyword((int) $flow['uid'], $phrase)) {
                // uid diketahui dari langkah 1; hanya verifikasi kata kunci, tanpa query ulang.
                $_SESSION['forgot_error'] = 'Kata kunci salah. Coba lagi, atau pilih jalur link email.';
                header('Location: /geprek-geh/auth/forgot'); exit;
            }

            $_SESSION['recovery_uid']   = (int) $flow['uid'];
            $_SESSION['recovery_name']  = $flow['name'];
            $_SESSION['recovery_email'] = $flow['email'];
            $_SESSION['recovery_exp']   = time() + 600; // 10 menit
            unset($_SESSION['forgot_flow']);
            header('Location: /geprek-geh/auth/recovery'); exit;
        }

        // ── LANGKAH 2 jalur email: link reset terkirim ──
        if (!RateLimiter::attempt('reset-ip:' . $ip, 5, 3600)) {
            $_SESSION['forgot_error'] = 'Terlalu banyak permintaan reset dari perangkat ini. Coba lagi dalam 1 jam.';
            header('Location: /geprek-geh/auth/forgot'); exit;
        }
        if (!RateLimiter::attempt('reset:' . $email, 3, 3600)) {
            $_SESSION['forgot_error'] = 'Terlalu banyak permintaan reset. Coba lagi dalam 1 jam.';
            header('Location: /geprek-geh/auth/forgot'); exit;
        }

        $user = $db->fetchOne("SELECT * FROM users WHERE email = ?", [$email]);
        if ($user) {
            $selector = bin2hex(random_bytes(16));           // public, in URL
            $token    = bin2hex(random_bytes(32));           // secret
            $hash     = hash('sha256', $token);
            // Expire 60 min from now — stored via MySQL DATE_ADD so PHP and MySQL share the same clock,
            // avoiding tz drift between getTimezone() and NOW().
            $expires  = null;

            // Invalidate prior outstanding tokens for this user
            $db->query("UPDATE password_resets SET used_at = NOW() WHERE user_id = ? AND used_at IS NULL", [$user['id']]);

            $newId = $db->insert('password_resets', [
                'user_id'    => $user['id'],
                'selector'   => $selector,
                'token_hash' => $hash,
            ]);
            // Set expiry using MySQL clock to avoid PHP/MySQL tz drift
            $db->query("UPDATE password_resets SET expires_at = DATE_ADD(NOW(), INTERVAL ? MINUTE) WHERE id = ?", [self::TTL_MINUTES, $newId]);

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
        $valid = self::validateToken($selector, $token) !== false;
        require __DIR__ . '/../views/layouts/auth-header.php';
        require __DIR__ . '/../views/auth/reset.php';
        require __DIR__ . '/../views/layouts/auth-footer.php';
    }

    public function isValidLink(string $selector, string $token): bool {
        return self::validateToken($selector, $token) !== false;
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
        if (strlen($password) > 72) {
            flash_set('error', 'Password maksimal 72 karakter.');
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

        // Password berubah → token "remember me" & sesi aktif lain tidak berlaku lagi.
        Auth::purgeRememberTokens((int) $row['user_id']);
        Auth::clearRememberCookie();
        $db->delete('sessions', 'user_id = ?', [(int) $row['user_id']]);

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