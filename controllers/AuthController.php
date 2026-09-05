<?php
class AuthController {
    public function loginForm() {
        if (Auth::check()) redirect('/geprek-geh/');
        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/auth/login.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }

    public function login() {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $user = Auth::login($email, $password);
        if (!$user) {
            flash_set('error', 'Email atau password salah.');
            header('Location: /geprek-geh/auth/login');
            exit;
        }

        // 2FA aktif → lanjut ke langkah verifikasi
        if ((int) $user['totp_enabled'] === 1) {
            $_SESSION['twofa_uid']   = $user['id'];
            $_SESSION['twofa_name']  = $user['name'];
            $_SESSION['twofa_role']  = $user['role'];
            flash_set('info', 'Masukkan kode verifikasi 2FA untuk melanjutkan.');
            header('Location: /geprek-geh/auth/2fa');
            exit;
        }

        Auth::establishSession($user);
        flash_set('success', 'Selamat datang, ' . $user['name'] . '!');
        $redirect = $user['role'] === 'admin' ? '/geprek-geh/admin' : '/geprek-geh/';
        header("Location: {$redirect}");
        exit;
    }

    public function twoFactorForm() {
        if (Auth::check()) redirect('/geprek-geh/');
        if (empty($_SESSION['twofa_uid'])) redirect('/geprek-geh/auth/login');
        $app = require __DIR__ . '/../config/app.php';
        $twofa_name = $_SESSION['twofa_name'] ?? '';
        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/auth/twofactor.php';
        require __DIR__ . '/../views/layouts/footer.php';
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

        // Kode TOTP dari aplikasi authenticator
        if (Totp::verify($user['totp_secret'], $code)) {
            Auth::establishSession($user);
            flash_set('success', 'Login berhasil!');
            $redirect = $user['role'] === 'admin' ? '/geprek-geh/admin' : '/geprek-geh/';
            header("Location: {$redirect}");
            exit;
        }

        // Recovery code sekali pakai
        $remaining = Totp::matchRecovery($user['totp_recovery'], $code);
        if ($remaining !== null) {
            $db->update('users', ['totp_recovery' => json_encode($remaining)], 'id = ?', [$user['id']]);
            Auth::establishSession($user);
            flash_set('success', 'Login berhasil via kode pemulihan (kode tidak dapat dipakai lagi).');
            $redirect = $user['role'] === 'admin' ? '/geprek-geh/admin' : '/geprek-geh/';
            header("Location: {$redirect}");
            exit;
        }

        flash_set('error', 'Kode 2FA salah atau sudah kedaluwarsa.');
        header('Location: /geprek-geh/auth/2fa');
        exit;
    }

    public function registerForm() {
        if (Auth::check()) redirect('/geprek-geh/');
        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/auth/register.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }

    public function register() {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $phone = trim($_POST['phone'] ?? '');

        if (strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
            flash_set('error', 'Data tidak valid. Minimal 6 karakter untuk password.');
            header('Location: /geprek-geh/auth/register');
            exit;
        }

        if (Auth::register($name, $email, $password, $phone)) {
            flash_set('success', 'Registrasi berhasil!');
            header('Location: /geprek-geh/');
        } else {
            flash_set('error', 'Email sudah terdaftar.');
            header('Location: /geprek-geh/auth/register');
        }
        exit;
    }

    public function logout() {
        Auth::logout();
    }
}
