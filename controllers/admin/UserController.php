<?php
namespace Admin;
class UserController {

    private function buildWhere(): array {
        $where = [];
        $params = [];

        $q = trim($_GET['q'] ?? '');
        if ($q !== '') {
            $where[] = "(u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
            $params[] = "%{$q}%";
            $params[] = "%{$q}%";
            $params[] = "%{$q}%";
        }

        $role = $_GET['role'] ?? '';
        if (in_array($role, ['admin', 'customer'])) {
            $where[] = 'u.role = ?';
            $params[] = $role;
        }

        $blocked = $_GET['blocked'] ?? '';
        if (in_array($blocked, ['0', '1'])) {
            $where[] = 'u.is_blocked = ?';
            $params[] = (int) $blocked;
        }

        $sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        return [$sql, $params];
    }

    public function index() {
        \Auth::requireAdmin();
        $db = \Database::getInstance();

        $q      = trim($_GET['q'] ?? '');
        $role   = $_GET['role'] ?? '';
        $blocked = $_GET['blocked'] ?? '';
        $sort   = $_GET['sort'] ?? 'terbaru';
        $per    = in_array((int) ($_GET['per'] ?? 15), [10, 15, 25, 50]) ? (int) $_GET['per'] : 15;
        $sort   = in_array($sort, ['terbaru', 'terlama', 'nama', 'email', 'role']) ? $sort : 'terbaru';
        $page   = max(1, (int) ($_GET['page'] ?? 1));

        [$whereSql, $whereParams] = $this->buildWhere();

        $kpis = $db->fetchOne(
            "SELECT COUNT(*) AS total,
                    SUM(role = 'admin') AS admin_count,
                    SUM(role = 'customer') AS customer_count,
                    SUM(is_blocked = 1) AS blocked_count
             FROM users"
        );

        $filteredCount = (int) $db->fetchOne(
            "SELECT COUNT(*) AS c FROM users u {$whereSql}",
            $whereParams
        )['c'];

        $total_pages = max(1, (int) ceil($filteredCount / $per));
        if ($page > $total_pages) $page = $total_pages;
        $offset = ($page - 1) * $per;

        $sortMap = [
            'terbaru' => 'u.created_at DESC',
            'terlama' => 'u.created_at ASC',
            'nama'    => 'u.name ASC',
            'email'   => 'u.email ASC',
            'role'    => 'FIELD(u.role, "admin", "customer")',
        ];
        $orderBy = $sortMap[$sort] ?? 'u.created_at DESC';

        $users = $db->fetchAll(
            "SELECT u.*, (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.id) AS order_count
             FROM users u {$whereSql}
             ORDER BY {$orderBy}
             LIMIT {$per} OFFSET {$offset}",
            $whereParams
        );

        $formErrors = \form_errors();
        $formOld    = \form_old();

        require __DIR__ . '/../../views/layouts/admin-header.php';
        require __DIR__ . '/../../views/admin/users/index.php';
        require __DIR__ . '/../../views/layouts/admin-footer.php';
    }

    public function show($id) {
        \Auth::requireAdmin();
        $id   = (int) $id;
        $db   = \Database::getInstance();
        $user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$user) {
            \flash_set('error', 'Pengguna tidak ditemukan.');
            header('Location: /geprek-geh/admin/users');
            exit;
        }

        $stats = $db->fetchOne(
            "SELECT COUNT(*) AS order_count,
                    SUM(CASE WHEN status != 'cancelled' THEN total + shipping_cost + tax - discount ELSE 0 END) AS total_spent,
                    SUM(status = 'pending')   AS pending_count,
                    SUM(status = 'processing') AS processing_count,
                    SUM(status = 'shipped')    AS shipped_count,
                    SUM(status = 'delivered')  AS delivered_count,
                    SUM(status = 'cancelled')  AS cancelled_count,
                    MAX(created_at) AS last_order_at
             FROM orders WHERE user_id = ?",
            [$id]
        );

        $orders       = $db->fetchAll("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 10", [$id]);
        $addresses    = $db->fetchAll("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC", [$id]);
        $notifications = $db->fetchAll("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10", [$id]);
        $sessions     = $db->fetchAll("SELECT * FROM sessions WHERE user_id = ? ORDER BY is_current DESC, created_at DESC", [$id]);

        $address_count  = (int) $db->fetchOne("SELECT COUNT(*) AS c FROM addresses WHERE user_id = ?", [$id])['c'];
        $notif_count    = (int) $db->fetchOne("SELECT COUNT(*) AS c FROM notifications WHERE user_id = ?", [$id])['c'];
        $notif_unread   = (int) $db->fetchOne("SELECT COUNT(*) AS c FROM notifications WHERE user_id = ? AND is_read = 0", [$id])['c'];

        require __DIR__ . '/../../views/layouts/admin-header.php';
        require __DIR__ . '/../../views/admin/users/show.php';
        require __DIR__ . '/../../views/layouts/admin-footer.php';
    }

    public function store() {
        \Auth::requireAdmin();
        if (!\verify_csrf()) { \flash_set('error', 'Token tidak valid.'); header('Location: /geprek-geh/admin/users'); exit; }
        $db  = \Database::getInstance();
        $in  = $this->collectInput();
        $err = $this->validate($in, null, true);
        if ($err) {
            \form_stash($err, $in);
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
        $db  = \Database::getInstance();
        $user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$user) {
            \flash_set('error', 'Pengguna tidak ditemukan.');
            header('Location: /geprek-geh/admin/users'); exit;
        }
        $in  = $this->collectInput();
        $err = $this->validate($in, $id);
        if ($err) {
            \form_stash($err, $in);
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

    public function block($id) {
        \Auth::requireAdmin();
        $id = (int) $id;
        if (!\verify_csrf()) { \flash_set('error', 'Token tidak valid.'); header('Location: /geprek-geh/admin/users'); exit; }
        if ($id === \Auth::id()) {
            \flash_set('error', 'Tidak bisa memblokir akun sendiri.');
            header('Location: /geprek-geh/admin/users');
            exit;
        }
        $db   = \Database::getInstance();
        $user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$user) {
            \flash_set('error', 'Pengguna tidak ditemukan.');
            header('Location: /geprek-geh/admin/users');
            exit;
        }

        $isCurrentlyBlocked = (int) ($user['is_blocked'] ?? 0) === 1;
        if ($isCurrentlyBlocked) {
            $db->update('users', ['is_blocked' => 0, 'blocked_at' => null], 'id = ?', [$id]);
            \flash_set('success', 'Akun "' . $user['name'] . '" telah dibuka blokirnya.');
        } else {
            $db->update('users', ['is_blocked' => 1, 'blocked_at' => date('Y-m-d H:i:s')], 'id = ?', [$id]);
            \Auth::purgeRememberTokens($id);
            \flash_set('success', 'Akun "' . $user['name'] . '" telah diblokir.');
        }

        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if (strpos($referer, '/geprek-geh/admin/users/' . $id) !== false) {
            header('Location: /geprek-geh/admin/users/' . $id);
        } else {
            header('Location: /geprek-geh/admin/users');
        }
        exit;
    }

    public function destroy($id) {
        \Auth::requireAdmin();
        $id = (int) $id;
        if (!\verify_csrf()) { \flash_set('error', 'Token tidak valid.'); header('Location: /geprek-geh/admin/users'); exit; }
        if ($id === \Auth::id()) {
            \flash_set('error', 'Tidak bisa menghapus akun sendiri.');
            header('Location: /geprek-geh/admin/users');
            exit;
        }
        $db   = \Database::getInstance();
        $user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$user) {
            \flash_set('error', 'Pengguna tidak ditemukan.');
            header('Location: /geprek-geh/admin/users');
            exit;
        }

        $db->delete('remember_tokens', 'user_id = ?', [$id]);
        $db->delete('sessions', 'user_id = ?', [$id]);
        $db->delete('users', 'id = ?', [$id]);

        \flash_set('success', 'Akun "' . $user['name'] . '" beserta seluruh datanya telah dihapus.');
        header('Location: /geprek-geh/admin/users');
        exit;
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
}
