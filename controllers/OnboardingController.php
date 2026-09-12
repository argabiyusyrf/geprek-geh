<?php
/**
 * Setup awal akun (wizard) — "kata kunci" wajib, alamat opsional.
 * Muncul pertama kali setelah registrasi; bisa dilewati sementara.
 */
class OnboardingController {

    public function show() {
        Auth::requireLogin();
        // Sudah lengkap (kata kunci + minimal 1 alamat) → tidak perlu lagi.
        if (Auth::keywordSet() && $this->hasAddress(Auth::id())) {
            redirect('/geprek-geh/');
        }
        $setup_errors = $_SESSION['setup_errors'] ?? null;
        unset($_SESSION['setup_errors']);
        $page_title = 'Lengkapi Akun';
        require __DIR__ . '/../views/layouts/auth-header.php';
        require __DIR__ . '/../views/account/setup.php';
        require __DIR__ . '/../views/layouts/auth-footer.php';
    }

    public function save() {
        Auth::requireLogin();
        if (!verify_csrf()) {
            flash_set('error', 'Token tidak valid.');
            redirect('/geprek-geh/account/setup');
        }
        $db = Database::getInstance();
        $uid = Auth::id();

        $keyword = $_POST['keyword'] ?? '';
        $addr = array_map('trim', [
            'recipient_name' => $_POST['recipient_name'] ?? '',
            'phone'          => $_POST['phone'] ?? '',
            'address'        => $_POST['address'] ?? '',
            'province'       => $_POST['province'] ?? '',
            'city'           => $_POST['city'] ?? '',
            'district'       => $_POST['district'] ?? '',
            'village'        => $_POST['village'] ?? '',
            'postal_code'    => $_POST['postal_code'] ?? '',
            'notes'          => $_POST['notes'] ?? '',
        ]);

        $errors = [];

        $klen = mb_strlen($keyword);
        if ($klen < 6)       $errors['keyword'] = 'Kata kunci minimal 6 karakter.';
        elseif ($klen > 72)  $errors['keyword'] = 'Kata kunci maksimal 72 karakter.';

        // Alamat opsional: kalau ada niat diisi, wajib lengkap minimal (nama & alamat).
        $addressIntent = $addr['recipient_name'] !== '' || $addr['address'] !== '';
        if ($addressIntent) {
            if (mb_strlen($addr['recipient_name']) < 2) $errors['recipient_name'] = 'Nama penerima minimal 2 karakter.';
            if ($addr['address'] === '')                $errors['address'] = 'Alamat lengkap wajib diisi.';
            if ($addr['postal_code'] !== '' && !preg_match('/^\d{5}$/', $addr['postal_code'])) {
                $errors['postal_code'] = 'Kode pos harus 5 digit angka.';
            }
        }

        if ($errors) {
            $_SESSION['setup_errors'] = $errors;
            redirect('/geprek-geh/account/setup');
        }

        Auth::setRecoveryKeyword($uid, $keyword);
        unset($_SESSION['skip_setup']);

        if ($addressIntent) {
            $isFirst = (int) $db->count('addresses', 'user_id = ?', [$uid]) === 0;
            if (!$isFirst) {
                $db->update('addresses', ['is_default' => 0], 'user_id = ?', [$uid]);
            }
            $db->insert('addresses', array_merge($addr, [
                'user_id'    => $uid,
                'is_default' => 1,
            ]));
        }

        flash_set('success', 'Setup akun selesai! Selamat datang di Geprek Geh.');
        redirect('/geprek-geh/');
    }

    /** Lewati setup untuk sekarang — bisa di-set dari Akun > Keamanan. */
    public function skip() {
        Auth::requireLogin();
        if (!verify_csrf()) {
            flash_set('error', 'Token tidak valid.');
            redirect('/geprek-geh/account/setup');
        }
        $_SESSION['skip_setup'] = 1;
        flash_set('info', 'Kata kunci akun bisa kamu atur nanti dari menu Akun → Keamanan.');
        redirect('/geprek-geh/');
    }

    private function hasAddress(int $uid): bool {
        return (int) Database::getInstance()->count('addresses', 'user_id = ?', [$uid]) > 0;
    }
}