<?php
/**
 * Pemulihan akun via "kata kunci" — tanpa email pihak ketiga.
 * Kata kunci terverifikasi di PasswordResetController::request (jalur keyword),
 * lalu sesi recovery berumur 10 menit dibuka di sini dengan 2 pilihan:
 *   - Login langsung (jadi sesi login, tetap hormati 2FA bila aktif)
 *   - Ubah password
 */
class RecoveryController {
    private const TTL = 600; // 10 menit

    private function recoveryUser() {
        if (empty($_SESSION['recovery_uid']) || empty($_SESSION['recovery_exp'])) return null;
        if ((int) $_SESSION['recovery_exp'] < time()) {
            $this->forgetRecovery();
            return null;
        }
        $user = Database::getInstance()->fetchOne("SELECT * FROM users WHERE id = ?", [(int) $_SESSION['recovery_uid']]);
        if (!$user) {
            $this->forgetRecovery();
            return null;
        }
        return $user;
    }

    private function forgetRecovery(): void {
        unset($_SESSION['recovery_uid'], $_SESSION['recovery_name'], $_SESSION['recovery_email'], $_SESSION['recovery_exp']);
    }

    /** Halaman pilihan: Login langsung / Ubah password. */
    public function choose() {
        $user = $this->recoveryUser();
        if (!$user) {
            flash_set('error', 'Sesi pemulihan tidak valid atau kedaluwarsa. Mulai lagi dari lupa password.');
            header('Location: /geprek-geh/auth/forgot'); exit;
        }
        $rc_name  = $_SESSION['recovery_name'] ?? $user['name'];
        $rc_email = $_SESSION['recovery_email'] ?? $user['email'];
        $rc_exp   = (int) ceil((($_SESSION['recovery_exp'] ?? time()) - time()) / 60);
        $page_title = 'Pemulihan Akun';
        require __DIR__ . '/../views/layouts/auth-header.php';
        require __DIR__ . '/../views/auth/recovery.php';
        require __DIR__ . '/../views/layouts/auth-footer.php';
    }

    /** Login langsung dari hasil verifikasi kata kunci. */
    public function loginDirect() {
        if (!verify_csrf()) {
            flash_set('error', 'Sesi tidak valid, silakan coba lagi.');
            header('Location: /geprek-geh/auth/recovery'); exit;
        }
        $user = $this->recoveryUser();
        if (!$user) {
            flash_set('error', 'Sesi pemulihan tidak valid atau kedaluwarsa.');
            header('Location: /geprek-geh/auth/forgot'); exit;
        }
        $remember = isset($_POST['remember']) && $_POST['remember'] === '1';
        $this->forgetRecovery();

        // 2FA tetap wajib dijalankan, sekalipun sudah lolos kata kunci.
        if ((int) $user['totp_enabled'] === 1) {
            $_SESSION['twofa_uid']      = (int) $user['id'];
            $_SESSION['twofa_name']     = $user['name'];
            $_SESSION['twofa_role']     = $user['role'];
            $_SESSION['twofa_remember'] = $remember ? 1 : 0;
            flash_set('info', 'Kata kunci benar. Masukkan kode verifikasi 2FA untuk melanjutkan.');
            header('Location: /geprek-geh/auth/2fa'); exit;
        }

        Auth::finalizeLogin($user, $remember);
    }

    /** Form ubah password (kata kunci sudah terverifikasi). */
    public function changeForm() {
        $user = $this->recoveryUser();
        if (!$user) {
            flash_set('error', 'Sesi pemulihan tidak valid atau kedaluwarsa.');
            header('Location: /geprek-geh/auth/forgot'); exit;
        }
        $rc_name = $_SESSION['recovery_name'] ?? $user['name'];
        $page_title = 'Ubah Password';
        require __DIR__ . '/../views/layouts/auth-header.php';
        require __DIR__ . '/../views/auth/recovery-change.php';
        require __DIR__ . '/../views/layouts/auth-footer.php';
    }

    /** Proses ubah password via kata kunci. */
    public function change() {
        if (!verify_csrf()) {
            flash_set('error', 'Sesi tidak valid, silakan coba lagi.');
            header('Location: /geprek-geh/auth/recovery/ubah'); exit;
        }
        $user = $this->recoveryUser();
        if (!$user) {
            flash_set('error', 'Sesi pemulihan tidak valid atau kedaluwarsa.');
            header('Location: /geprek-geh/auth/forgot'); exit;
        }

        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['password_confirm'] ?? '';

        if (strlen($password) < 6)      { flash_set('error', 'Password minimal 6 karakter.');  header('Location: /geprek-geh/auth/recovery/ubah'); exit; }
        if (strlen($password) > 72)     { flash_set('error', 'Password maksimal 72 karakter.'); header('Location: /geprek-geh/auth/recovery/ubah'); exit; }
        if ($password !== $confirm)     { flash_set('error', 'Konfirmasi password tidak cocok.'); header('Location: /geprek-geh/auth/recovery/ubah'); exit; }

        $db = Database::getInstance();
        $uid = (int) $user['id'];
        $db->update('users', ['password' => password_hash($password, PASSWORD_DEFAULT)], 'id = ?', [$uid]);

        // Password berubah → token remember-sesi lain & token "ingat saya" dicabut.
        Auth::purgeRememberTokens($uid);
        Auth::clearRememberCookie();
        $db->delete('sessions', 'user_id = ?', [$uid]);
        $this->forgetRecovery();

        flash_set('success', 'Password berhasil diperbarui.');
        header('Location: /geprek-geh/auth/login'); exit;
    }
}