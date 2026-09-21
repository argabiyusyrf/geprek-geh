<?php
/**
 * Pemulihan akun via "kata kunci" — tanpa email pihak ketiga.
 * Kata kunci terverifikasi di langkah 2 wizard lupa-password,
 * lalu sesi recovery berumur 10 menit mengarah langsung ke form ubah password.
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

    /** Form ubah password (kata kunci sudah terverifikasi). */
    public function changeForm() {
        $user = $this->recoveryUser();
        if (!$user) {
            flash_set('error', 'Sesi pemulihan tidak valid atau kedaluwarsa.');
            gg_redirect('/auth/forgot'); exit;
        }
        $rc_name = $_SESSION['recovery_name'] ?? $user['name'];
        $page_title = 'Ubah Password';
        render('auth/recovery', get_defined_vars());
    }

    /** Proses ubah password via kata kunci. */
    public function change() {
        if (!verify_csrf()) {
            flash_set('error', 'Sesi tidak valid, silakan coba lagi.');
            gg_redirect('/auth/recovery'); exit;
        }
        $user = $this->recoveryUser();
        if (!$user) {
            flash_set('error', 'Sesi pemulihan tidak valid atau kedaluwarsa.');
            gg_redirect('/auth/forgot'); exit;
        }

        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['password_confirm'] ?? '';

        if (strlen($password) < 6)      { flash_set('error', 'Password minimal 6 karakter.');  gg_redirect('/auth/recovery'); exit; }
        if (strlen($password) > 72)     { flash_set('error', 'Password maksimal 72 karakter.'); gg_redirect('/auth/recovery'); exit; }
        if ($password !== $confirm)     { flash_set('error', 'Konfirmasi password tidak cocok.'); gg_redirect('/auth/recovery'); exit; }

        $db = Database::getInstance();
        $uid = (int) $user['id'];
        $db->update('users', ['password' => password_hash($password, PASSWORD_DEFAULT)], 'id = ?', [$uid]);

        // Password berubah → token remember-sesi lain & token "ingat saya" dicabut.
        Auth::purgeRememberTokens($uid);
        Auth::clearRememberCookie();
        $db->delete('sessions', 'user_id = ?', [$uid]);
        $this->forgetRecovery();

        flash_set('success', 'Password berhasil diperbarui.');
        gg_redirect('/auth/login'); exit;
    }
}