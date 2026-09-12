<?php
namespace Admin;
class UserController {
    public function index() {
        \Auth::requireAdmin();
        $db = \Database::getInstance();
        $users = $db->fetchAll(
            "SELECT u.*, (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) AS order_count
             FROM users u ORDER BY u.created_at DESC"
        );

        $formErrors = \form_errors();
        $formOld    = \form_old();

        require __DIR__ . '/../../views/layouts/admin-header.php';
        require __DIR__ . '/../../views/admin/users/index.php';
        require __DIR__ . '/../../views/layouts/admin-footer.php';
    }

    private function collectInput(): array {
        return [
            'name'         => trim($_POST['name'] ?? ''),
            'email'        => strtolower(trim($_POST['email'] ?? '')),
            'phone'        => trim($_POST['phone'] ?? ''),
            'role'         => ($_POST['role'] ?? 'customer') === 'admin' ? 'admin' : 'customer',
            'notify_email' => isset($_POST['notify_email']) ? 1 : 0,
            'password'     => $_POST['password'] ?? '',
        ];
    }

    private function validate(array $in, ?int $ignoreId = null, bool $requirePassword = false): array {
        $errors = [];
        $db = \Database::getInstance();
        if ($in['name'] === '') {
            $errors['name'] = 'Nama lengkap wajib diisi.';
        }
        if (!filter_var($in['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format email tidak valid.';
        } elseif ($db->fetchOne("SELECT id FROM users WHERE email = ? AND id != ?", [$in['email'], (int) ($ignoreId ?? 0)])) {
            $errors['email'] = 'Email sudah terdaftar.';
        }
        if ($in['phone'] !== '' && !preg_match('/^[0-9+\-\s]{8,16}$/', $in['phone'])) {
            $errors['phone'] = 'Nomor telepon tidak valid.';
        }
        if ($requirePassword || $in['password'] !== '') {
            if (strlen($in['password']) < 8) {
                $errors['password'] = 'Password minimal 8 karakter.';
            }
        }
        return $errors;
    }

    public function store() {
        \Auth::requireAdmin();
        if (!\verify_csrf()) { \flash_set('error', 'Token tidak valid.'); header('Location: /geprek-geh/admin/users'); exit; }
        $db = \Database::getInstance();
        $in = $this->collectInput();

        $errors = $this->validate($in, null, true);
        if ($errors) {
            \form_stash($errors, $in);
            \flash_set('error', 'Mohon periksa kembali isian form pengguna.');
            header('Location: /geprek-geh/admin/users?create=1&error=1'); exit;
        }

        $db->insert('users', [
            'name'         => $in['name'],
            'email'        => $in['email'],
            'password'     => password_hash($in['password'], PASSWORD_DEFAULT),
            'phone'        => $in['phone'] ?: null,
            'role'         => $in['role'],
            'notify_email' => $in['notify_email'],
        ]);

        \flash_set('success', 'Pengguna "' . $in['name'] . '" berhasil ditambahkan.');
        header('Location: /geprek-geh/admin/users');
        exit;
    }

    public function update($id) {
        \Auth::requireAdmin();
        $id = (int) $id;
        if (!\verify_csrf()) { \flash_set('error', 'Token tidak valid.'); header('Location: /geprek-geh/admin/users'); exit; }
        $db = \Database::getInstance();
        $user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$user) {
            \flash_set('error', 'Pengguna tidak ditemukan.');
            header('Location: /geprek-geh/admin/users'); exit;
        }
        $in = $this->collectInput();

        $errors = $this->validate($in, $id);
        if ($errors) {
            \form_stash($errors, $in);
            \flash_set('error', 'Mohon periksa kembali isian form pengguna.');
            header('Location: /geprek-geh/admin/users?edit=' . $id . '&error=1'); exit;
        }

        $data = [
            'name'         => $in['name'],
            'email'        => $in['email'],
            'phone'        => $in['phone'] ?: null,
            'role'         => $in['role'],
            'notify_email' => $in['notify_email'],
        ];
        if ($in['password'] !== '') {
            $data['password'] = password_hash($in['password'], PASSWORD_DEFAULT);
        }

        $db->update('users', $data, 'id = ?', [$id]);
        \flash_set('success', 'Data pengguna berhasil diupdate.');
        header('Location: /geprek-geh/admin/users');
        exit;
    }
}