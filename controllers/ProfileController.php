<?php
class ProfileController {

    private const TABS = ['overview', 'profil', 'security', 'addresses', 'settings'];

    private function currentTab() {
        $tab = $_GET['tab'] ?? 'overview';
        return in_array($tab, self::TABS, true) ? $tab : 'overview';
    }

    private function addresses($uid) {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, updated_at DESC",
            [$uid]
        );
    }

    public function index() {
        Auth::requireLogin();
        $db = Database::getInstance();
        $user = Auth::user();
        $tab = $this->currentTab();

        $view_data = [];

        // —— Profil tab ——
        $old = $_SESSION['profile_old'] ?? null;
        $view_data['name'] = $old['name'] ?? $user['name'];
        $view_data['phone'] = $old['phone'] ?? $user['phone'];
        $view_data['address'] = $old['address'] ?? $user['address'];
        $view_data['profile_errors'] = $_SESSION['profile_errors'] ?? [];
        unset($_SESSION['profile_errors'], $_SESSION['profile_old']);

        // —— Security tab ——
        $view_data['pwd_errors'] = $_SESSION['profile_pwd_errors'] ?? [];
        unset($_SESSION['profile_pwd_errors']);

        // —— Addresses tab ——
        $view_data['addresses'] = $this->addresses(Auth::id());
        $addr_old = $_SESSION['address_old'] ?? null;
        $view_data['address_old'] = $addr_old;
        $view_data['edit_id'] = $_SESSION['address_edit_id'] ?? null;
        $view_data['address_errors'] = $_SESSION['address_errors'] ?? [];
        unset($_SESSION['address_old'], $_SESSION['address_edit_id'], $_SESSION['address_errors']);

        // —— Overview tab ——
        $view_data['recent_orders'] = $db->fetchAll(
            "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 1",
            [Auth::id()]
        );
        $order_all = $db->fetchAll("SELECT status, grand_total FROM orders WHERE user_id = ?", [Auth::id()]);
        $view_data['order_count'] = count($order_all);
        $view_data['order_total'] = array_sum(array_column($order_all, 'grand_total'));
        $status_map = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        $counts = array_count_values(array_column($order_all, 'status'));
        $view_data['top_status'] = null;
        $view_data['top_status_count'] = 0;
        foreach ($status_map as $st) {
            if (($counts[$st] ?? 0) > $view_data['top_status_count']) {
                $view_data['top_status'] = $st;
                $view_data['top_status_count'] = $counts[$st];
            }
        }

        $view_data['tab'] = $tab;
        $view_data['user'] = $user;

        // —— 2FA tab (Keamanan) ——
        $view_data['twofa_setup']   = $_SESSION['twofa_setup_secret'] ?? null;
        $view_data['twofa_uri']     = $view_data['twofa_setup'] ? Totp::otpauthUri($user['email'], $view_data['twofa_setup']) : '';
        $view_data['twofa_recovery'] = $_SESSION['twofa_recovery_codes'] ?? null;
        $view_data['twofa_error']    = $_SESSION['twofa_error'] ?? null;
        unset($_SESSION['twofa_recovery_codes'], $_SESSION['twofa_error']);

        extract($view_data);

        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/account/profile.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }

    public function update() {
        Auth::requireLogin();
        if (!verify_csrf()) {
            flash_set('error', 'Token tidak valid.');
            redirect('/geprek-geh/account');
        }
        $db = Database::getInstance();
        $user = Auth::user();

        $name = trim($_POST['name'] ?? '');
        $phone = preg_replace('/[^0-9]/', '', trim($_POST['phone'] ?? ''));
        if (strlen($phone) >= 11 && substr($phone, 0, 2) === '62') {
            $phone = '0' . substr($phone, 2);
        }
        $address = trim($_POST['address'] ?? '');

        $errors = [];
        if (mb_strlen($name) < 2) {
            $errors['name'] = 'Nama minimal 2 karakter.';
        } elseif (mb_strlen($name) > 100) {
            $errors['name'] = 'Nama terlalu panjang (maks. 100 karakter).';
        }
        if (empty($phone)) {
            $errors['phone'] = 'Nomor telepon wajib diisi.';
        } elseif (!preg_match('/^08\d{8,11}$/', $phone)) {
            $errors['phone'] = 'Format nomor tidak valid. Contoh: 081234567890.';
        }
        if (empty($address)) {
            $errors['address'] = 'Alamat wajib diisi.';
        } elseif (mb_strlen($address) > 500) {
            $errors['address'] = 'Alamat terlalu panjang (maks. 500 karakter).';
        }
        if ($errors) {
            $_SESSION['profile_old'] = ['name' => $name, 'phone' => $phone, 'address' => $address];
            $_SESSION['profile_errors'] = $errors;
            redirect('/geprek-geh/account?tab=profil');
        }

        $db->update('users', ['name' => $name, 'phone' => $phone, 'address' => $address], 'id = ?', [$user['id']]);
        $_SESSION['user_name'] = $name;
        flash_set('success', 'Profil berhasil diperbarui.');
        redirect('/geprek-geh/account?tab=profil');
    }

    public function changePassword() {
        Auth::requireLogin();
        if (!verify_csrf()) {
            flash_set('error', 'Token tidak valid.');
            redirect('/geprek-geh/account');
        }
        $db = Database::getInstance();
        $user = Auth::user();

        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['new_password_confirm'] ?? '';

        $errors = [];
        if (!password_verify($current, $user['password'])) {
            $errors['current_password'] = 'Password lama tidak sesuai.';
        }
        if (strlen($new) < 6) {
            $errors['new_password'] = 'Password baru minimal 6 karakter.';
        }
        if ($new !== $confirm) {
            $errors['new_password_confirm'] = 'Konfirmasi password tidak cocok.';
        }
        if ($errors) {
            $_SESSION['profile_pwd_errors'] = $errors;
            redirect('/geprek-geh/account?tab=security');
        }

        $db->update('users', ['password' => password_hash($new, PASSWORD_DEFAULT)], 'id = ?', [$user['id']]);
        flash_set('success', 'Password berhasil diubah.');
        redirect('/geprek-geh/account?tab=security');
    }

    // ─────────────────────────── 2FA (Autentikasi 2 Langkah) ───────────────────────────

    /** Mulai proses aktivasi: buat secret sementara (belum diaktifkan). */
    public function twoFactorSetup() {
        Auth::requireLogin();
        if (!verify_csrf()) {
            flash_set('error', 'Token tidak valid.');
            redirect('/geprek-geh/account?tab=security');
        }
        $user = Auth::user();
        if ((int) $user['totp_enabled'] === 1) {
            flash_set('error', '2FA sudah aktif.');
            redirect('/geprek-geh/account?tab=security');
        }
        unset($_SESSION['twofa_error']);
        $_SESSION['twofa_setup_secret'] = Totp::generateSecret();
        flash_set('info', 'Pindai QR atau masukkan kunci rahasia berikut ke aplikasi authenticator, lalu verifikasi dengan kode 6 digit.');
        redirect('/geprek-geh/account?tab=security');
    }

    /** Batalkan proses aktivasi yang belum selesai. */
    public function twoFactorCancel() {
        Auth::requireLogin();
        if (!verify_csrf()) {
            flash_set('error', 'Token tidak valid.');
            redirect('/geprek-geh/account?tab=security');
        }
        unset($_SESSION['twofa_setup_secret']);
        flash_set('success', 'Aktivasi 2FA dibatalkan.');
        redirect('/geprek-geh/account?tab=security');
    }

    /** Verifikasi kode dari authenticator untuk benar-benar mengaktifkan 2FA. */
    public function twoFactorConfirm() {
        Auth::requireLogin();
        if (!verify_csrf()) {
            flash_set('error', 'Token tidak valid.');
            redirect('/geprek-geh/account?tab=security');
        }
        $db = Database::getInstance();
        $user = Auth::user();
        $secret = $_SESSION['twofa_setup_secret'] ?? null;

        if ((int) $user['totp_enabled'] === 1 || !$secret) {
            unset($_SESSION['twofa_setup_secret']);
            flash_set('error', 'Sesi aktivasi 2FA tidak valid.');
            redirect('/geprek-geh/account?tab=security');
        }

        $code = trim($_POST['code'] ?? '');
        if (!Totp::verify($secret, $code)) {
            $_SESSION['twofa_error'] = 'Kode salah. Periksa kembali input kode dari aplikasi authenticator.';
            redirect('/geprek-geh/account?tab=security');
        }

        $codes = Totp::recoveryCodes();
        $hashes = array_map('password_hash', $codes, array_fill(0, count($codes), PASSWORD_DEFAULT));

        $db->update('users', [
            'totp_secret'   => $secret,
            'totp_enabled'  => 1,
            'totp_recovery' => json_encode($hashes),
        ], 'id = ?', [$user['id']]);

        unset($_SESSION['twofa_setup_secret']);
        $_SESSION['twofa_recovery_codes'] = $codes;
        flash_set('success', 'Autentikasi 2 langkah berhasil diaktifkan. Simpan kode pemulihan di tempat aman!');
        redirect('/geprek-geh/account?tab=security');
    }

    /** Nonaktifkan 2FA — wajib konfirmasi dengan kode TOTP atau recovery code. */
    public function twoFactorDisable() {
        Auth::requireLogin();
        if (!verify_csrf()) {
            flash_set('error', 'Token tidak valid.');
            redirect('/geprek-geh/account?tab=security');
        }
        $db = Database::getInstance();
        $user = Auth::user();
        if ((int) $user['totp_enabled'] !== 1) {
            flash_set('error', '2FA sedang nonaktif.');
            redirect('/geprek-geh/account?tab=security');
        }

        $code = trim($_POST['code'] ?? '');
        $ok = Totp::verify($user['totp_secret'], $code);

        // recovery code dipakai untuk menonaktifkan → kode tsb dihapus
        if (!$ok) {
            $remaining = Totp::matchRecovery($user['totp_recovery'], $code);
            if ($remaining !== null) {
                $db->update('users', ['totp_recovery' => json_encode($remaining)], 'id = ?', [$user['id']]);
                $ok = true;
            }
        }

        if (!$ok) {
            $_SESSION['twofa_error'] = 'Kode verifikasi salah. Gunakan kode dari aplikasi authenticator atau recovery code.';
            redirect('/geprek-geh/account?tab=security');
        }

        $db->update('users', ['totp_secret' => null, 'totp_enabled' => 0, 'totp_recovery' => null], 'id = ?', [$user['id']]);
        flash_set('success', 'Autentikasi 2 langkah dinonaktifkan.');
        redirect('/geprek-geh/account?tab=security');
    }

    /** Ganti recovery code (kode lama tidak berlaku). */
    public function twoFactorRegenerate() {
        Auth::requireLogin();
        if (!verify_csrf()) {
            flash_set('error', 'Token tidak valid.');
            redirect('/geprek-geh/account?tab=security');
        }
        $db = Database::getInstance();
        $user = Auth::user();
        if ((int) $user['totp_enabled'] !== 1) {
            flash_set('error', '2FA sedang nonaktif.');
            redirect('/geprek-geh/account?tab=security');
        }

        $codes = Totp::recoveryCodes();
        $hashes = array_map('password_hash', $codes, array_fill(0, count($codes), PASSWORD_DEFAULT));
        $db->update('users', ['totp_recovery' => json_encode($hashes)], 'id = ?', [$user['id']]);

        $_SESSION['twofa_recovery_codes'] = $codes;
        flash_set('success', 'Recovery code baru dibuat. Kode lama tidak berlaku lagi.');
        redirect('/geprek-geh/account?tab=security');
    }

    // ─────────────────────────── Address CRUD ───────────────────────────

    private function addressInputs() {
        return array_map('trim', [
            'label'          => $_POST['label'] ?? '',
            'recipient_name' => $_POST['recipient_name'] ?? '',
            'phone'          => $_POST['phone'] ?? '',
            'province'       => $_POST['province'] ?? '',
            'city'           => $_POST['city'] ?? '',
            'district'       => $_POST['district'] ?? '',
            'village'        => $_POST['village'] ?? '',
            'postal_code'    => $_POST['postal_code'] ?? '',
            'address'        => $_POST['address'] ?? '',
            'notes'          => $_POST['notes'] ?? '',
            'is_default'     => isset($_POST['is_default']) ? 1 : 0,
        ]);
    }

    private function addressSave() {
        Auth::requireLogin();
        if (!verify_csrf()) {
            flash_set('error', 'Token tidak valid.');
            redirect('/geprek-geh/account?tab=addresses');
        }

        $db = Database::getInstance();
        $uid = Auth::id();
        $in = $this->addressInputs();

        $errors = [];
        if (strlen($in['recipient_name']) < 2)      $errors['recipient_name'] = 'Nama penerima minimal 2 karakter.';
        if (empty($in['address']))                  $errors['address'] = 'Alamat lengkap wajib diisi.';
        if ($in['postal_code'] !== '' && !preg_match('/^\d{5}$/', $in['postal_code'])) {
            $errors['postal_code'] = 'Kode pos harus 5 digit angka.';
        }
        if ($errors) {
            $_SESSION['address_old'] = $in;
            $_SESSION['address_edit_id'] = null;
            $_SESSION['address_errors'] = $errors;
            redirect('/geprek-geh/account?tab=addresses');
        }

        $isFirst = (int) $db->count('addresses', 'user_id = ?', [$uid]) === 0;
        $in['is_default'] = $in['is_default'] || $isFirst ? 1 : 0;

        if ($in['is_default']) {
            $db->update('addresses', ['is_default' => 0], 'user_id = ?', [$uid]);
        }

        $id = $db->insert('addresses', array_merge($in, ['user_id' => $uid]));
        flash_set('success', 'Alamat berhasil ditambahkan.');
        redirect('/geprek-geh/account?tab=addresses');
        return $id;
    }

    public function store() {
        $this->addressSave();
    }

    // Prefill drawer untuk edit (server-side)
    public function openEdit($id) {
        Auth::requireLogin();
        $db = Database::getInstance();
        $uid = Auth::id();
        $id = (int) $id;
        $exists = $db->fetchOne("SELECT id FROM addresses WHERE id = ? AND user_id = ?", [$id, $uid]);
        if (!$exists) {
            flash_set('error', 'Alamat tidak ditemukan.');
            redirect('/geprek-geh/account?tab=addresses');
        }
        $_SESSION['address_edit_id'] = $id;
        unset($_SESSION['address_old'], $_SESSION['address_errors']);
        redirect('/geprek-geh/account?tab=addresses');
    }

    public function edit($id) {
        Auth::requireLogin();
        if (!verify_csrf()) {
            flash_set('error', 'Token tidak valid.');
            redirect('/geprek-geh/account?tab=addresses');
        }
        $db = Database::getInstance();
        $uid = Auth::id();
        $id = (int) $id;

        $exists = $db->fetchOne("SELECT id FROM addresses WHERE id = ? AND user_id = ?", [$id, $uid]);
        if (!$exists) {
            flash_set('error', 'Alamat tidak ditemukan.');
            redirect('/geprek-geh/account?tab=addresses');
        }

        $in = $this->addressInputs();

        $errors = [];
        if (strlen($in['recipient_name']) < 2)      $errors['recipient_name'] = 'Nama penerima minimal 2 karakter.';
        if (empty($in['address']))                  $errors['address'] = 'Alamat lengkap wajib diisi.';
        if ($in['postal_code'] !== '' && !preg_match('/^\d{5}$/', $in['postal_code'])) {
            $errors['postal_code'] = 'Kode pos harus 5 digit angka.';
        }
        if ($errors) {
            $_SESSION['address_old'] = $in;
            $_SESSION['address_edit_id'] = $id;
            $_SESSION['address_errors'] = $errors;
            redirect('/geprek-geh/account?tab=addresses');
        }

        if ($in['is_default']) {
            $db->update('addresses', ['is_default' => 0], 'user_id = ?', [$uid]);
        }
        unset($in['is_default']);

        $db->update('addresses', $in, 'id = ?', [$id]);
        flash_set('success', 'Alamat berhasil diperbarui.');
        redirect('/geprek-geh/account?tab=addresses');
    }

    public function setDefault($id) {
        Auth::requireLogin();
        if (!verify_csrf()) {
            flash_set('error', 'Token tidak valid.');
            redirect('/geprek-geh/account?tab=addresses');
        }
        $db = Database::getInstance();
        $uid = Auth::id();
        $id = (int) $id;

        $exists = $db->fetchOne("SELECT id FROM addresses WHERE id = ? AND user_id = ?", [$id, $uid]);
        if (!$exists) {
            flash_set('error', 'Alamat tidak ditemukan.');
            redirect('/geprek-geh/account?tab=addresses');
        }

        $db->update('addresses', ['is_default' => 0], 'user_id = ?', [$uid]);
        $db->update('addresses', ['is_default' => 1], 'id = ?', [$id]);
        flash_set('success', 'Alamat utama diperbarui.');
        redirect('/geprek-geh/account?tab=addresses');
    }

    public function delete($id) {
        Auth::requireLogin();
        if (!verify_csrf()) {
            flash_set('error', 'Token tidak valid.');
            redirect('/geprek-geh/account?tab=addresses');
        }
        $db = Database::getInstance();
        $uid = Auth::id();
        $id = (int) $id;

        $target = $db->fetchOne("SELECT * FROM addresses WHERE id = ? AND user_id = ?", [$id, $uid]);
        if (!$target) {
            flash_set('error', 'Alamat tidak ditemukan.');
            redirect('/geprek-geh/account?tab=addresses');
        }

        $db->delete('addresses', 'id = ?', [$id]);

        // jika alamat utama dihapus, promosi alamat lain jadi utama
        if ((int) $target['is_default'] === 1) {
            $next = $db->fetchOne("SELECT id FROM addresses WHERE user_id = ? ORDER BY updated_at DESC LIMIT 1", [$uid]);
            if ($next) {
                $db->update('addresses', ['is_default' => 1], 'id = ?', [$next['id']]);
            }
        }

        flash_set('success', 'Alamat berhasil dihapus.');
        redirect('/geprek-geh/account?tab=addresses');
    }
}