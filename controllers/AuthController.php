<?php
class AuthController {
    public function loginForm() {
        if (Auth::check()) redirect('/geprek-geh/');
        $login_old = $_SESSION['login_old'] ?? null;
        unset($_SESSION['login_old']);
        require __DIR__ . '/../views/layouts/auth-header.php';
        require __DIR__ . '/../views/auth/login.php';
        require __DIR__ . '/../views/layouts/auth-footer.php';
    }

    public function login() {
        $email    = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']) && $_POST['remember'] === '1';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
            $_SESSION['login_old'] = ['email' => $email];
            flash_set('error', 'Format email tidak valid.');
            header('Location: /geprek-geh/auth/login');
            exit;
        }

        if (!RateLimiter::attempt('login:' . $email, 5, 300)) {
            $_SESSION['login_old'] = ['email' => $email];
            flash_set('error', 'Terlalu banyak percobaan. Coba lagi dalam 5 menit.');
            header('Location: /geprek-geh/auth/login');
            exit;
        }

        $user = Auth::login($email, $password);
        if (!$user) {
            $_SESSION['login_old'] = ['email' => $email];
            flash_set('error', 'Email atau password salah.');
            header('Location: /geprek-geh/auth/login');
            exit;
        }

        // 2FA aktif → lanjut ke langkah verifikasi; bawa niat "remember" ke sesi.
        if ((int) $user['totp_enabled'] === 1) {
            $_SESSION['twofa_uid']      = $user['id'];
            $_SESSION['twofa_name']     = $user['name'];
            $_SESSION['twofa_role']     = $user['role'];
            $_SESSION['twofa_remember'] = $remember ? 1 : 0;
            flash_set('info', 'Masukkan kode verifikasi 2FA untuk melanjutkan.');
            header('Location: /geprek-geh/auth/2fa');
            exit;
        }

        Auth::finalizeLogin($user, $remember);
    }

    public function twoFactorForm() {
        if (Auth::check()) redirect('/geprek-geh/');
        if (empty($_SESSION['twofa_uid'])) redirect('/geprek-geh/auth/login');
        $app = require __DIR__ . '/../config/app.php';
        $twofa_name = $_SESSION['twofa_name'] ?? '';
        require __DIR__ . '/../views/layouts/auth-header.php';
        require __DIR__ . '/../views/auth/twofactor.php';
        require __DIR__ . '/../views/layouts/auth-footer.php';
    }

    public function twoFactorSubmit() {
        if (Auth::check()) redirect('/geprek-geh/');
        if (empty($_SESSION['twofa_uid'])) redirect('/geprek-geh/auth/login');

        $db = Database::getInstance();
        $user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [(int) $_SESSION['twofa_uid']]);

        if (!$user || (int) $user['totp_enabled'] !== 1) {
            unset($_SESSION['twofa_uid'], $_SESSION['twofa_name'], $_SESSION['twofa_role']);
            flash_set('error', 'Sesi tidak valid, silakan login kembali.');
            header('Location: /geprek-geh/auth/login');
            exit;
        }

        $code = trim($_POST['code'] ?? '');

        if (!RateLimiter::attempt('2fa:' . ($_SESSION['twofa_uid'] ?? 'x'), 5, 300)) {
            flash_set('error', 'Terlalu banyak percobaan 2FA. Coba lagi dalam 5 menit.');
            header('Location: /geprek-geh/auth/2fa');
            exit;
        }

        // Kode TOTP dari aplikasi authenticator
        if (Totp::verify($user['totp_secret'], $code)) {
            Auth::finalizeLogin($user, !empty($_SESSION['twofa_remember']));
        }

        // Recovery code sekali pakai
        $remaining = Totp::matchRecovery($user['totp_recovery'], $code);
        if ($remaining !== null) {
            $db->update('users', ['totp_recovery' => json_encode($remaining)], 'id = ?', [$user['id']]);
            Auth::finalizeLogin($user, !empty($_SESSION['twofa_remember']));
        }

        flash_set('error', 'Kode 2FA salah atau sudah kedaluwarsa.');
        header('Location: /geprek-geh/auth/2fa');
        exit;
    }

    public function registerForm() {
        if (Auth::check()) redirect('/geprek-geh/');
        $reg_old = $_SESSION['reg_old'] ?? null;
        $reg_errors = $_SESSION['reg_errors'] ?? null;
        unset($_SESSION['reg_old'], $_SESSION['reg_errors']);
        require __DIR__ . '/../views/layouts/auth-header.php';
        require __DIR__ . '/../views/auth/register.php';
        require __DIR__ . '/../views/layouts/auth-footer.php';
    }

    public function register() {
        $name     = trim($_POST['name'] ?? '');
        $email    = strtolower(trim($_POST['email'] ?? ''));
        $phone    = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['password_confirm'] ?? '';
        $terms    = $_POST['terms'] ?? '';

        $db = Database::getInstance();
        $errors = [];
        $fatal  = null; // error rate-limit → hentikan validasi lain + jangan konsumsi formulir detail

        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if (!RateLimiter::attempt('register:' . $ip, 10, 3600)) {
            $errors['_global'] = 'Terlalu banyak upaya pendaftaran dari perangkat ini. Coba lagi dalam 1 jam.';
            $fatal = true;
        }

        if ($fatal) {
            $_SESSION['reg_old'] = ['name' => $name, 'email' => $email, 'phone' => $phone, 'terms' => $terms === '1' ? '1' : ''];
            $_SESSION['reg_errors'] = $errors;
            header('Location: /geprek-geh/auth/register');
            exit;
        }

        if (mb_strlen($name) < 2) {
            $errors['name'] = 'Nama lengkap minimal 2 karakter.';
        } elseif (mb_strlen($name) > 100) {
            $errors['name'] = 'Nama terlalu panjang (maks 100 karakter).';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
            $errors['email'] = 'Format email tidak valid.';
        } elseif ($db->fetchOne("SELECT id FROM users WHERE email = ?", [$email])) {
            $errors['email'] = 'Email sudah terdaftar. Gunakan email lain atau silakan login.';
        }

        if ($phone !== '') {
            $phoneDigits = preg_replace('/\D/', '', $phone);
            if (str_starts_with($phoneDigits, '62')) $phoneDigits = '0' . substr($phoneDigits, 2);
            $phone = $phoneDigits;
            if (!preg_match('/^08\d{8,11}$/', $phone)) {
                $errors['phone'] = 'Nomor telepon tidak valid. Contoh: 081234567890.';
            }
        }

        if (strlen($password) < 6) {
            $errors['password'] = 'Password minimal 6 karakter.';
        } elseif (strlen($password) > 72) {
            $errors['password'] = 'Password maksimal 72 karakter.';
        }
        if ($password !== $confirm) {
            $errors['password_confirm'] = 'Konfirmasi password tidak cocok.';
        }

        if ($terms !== '1') {
            $errors['terms'] = 'Harap setujui Syarat & Ketentuan dan Kebijakan Privasi.';
        }

        if ($errors) {
            $_SESSION['reg_old']     = ['name' => $name, 'email' => $email, 'phone' => $phone, 'terms' => $terms === '1' ? '1' : ''];
            $_SESSION['reg_errors']  = $errors;
            header('Location: /geprek-geh/auth/register');
            exit;
        }

        if (Auth::register($name, $email, $password, $phone)) {
            flash_set('success', 'Registrasi berhasil! Selamat datang, ' . $name . '!');
            header('Location: /geprek-geh/account/setup');
        } else {
            $errors['email'] = 'Email sudah terdaftar. Gunakan email lain atau silakan login.';
            $_SESSION['reg_old']     = ['name' => $name, 'email' => $email, 'phone' => $phone, 'terms' => $terms === '1' ? '1' : ''];
            $_SESSION['reg_errors']  = $errors;
            header('Location: /geprek-geh/auth/register');
        }
        exit;
    }

    public function logout() {
        Auth::logout();
    }
}
