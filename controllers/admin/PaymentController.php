<?php
namespace Admin;
class PaymentController {

    private function isUniqueCode($db, $code, $ignoreId = null) {
        $where = 'code = ?';
        $params = [$code];
        if ($ignoreId) {
            $where .= ' AND id != ?';
            $params[] = (int) $ignoreId;
        }
        return !(bool) $db->fetchColumn("SELECT COUNT(*) FROM payment_methods WHERE {$where}", $params);
    }

    private function validate($post) {
        $type = $post['type'] ?? 'bank';
        if (!in_array($type, ['bank', 'ewallet'], true)) $type = 'bank';
        $name = mb_substr(trim($post['name'] ?? ''), 0, 100);
        $number = mb_substr(trim($post['number'] ?? ''), 0, 50);
        $holder = mb_substr(trim($post['holder'] ?? ''), 0, 100);
        $description = mb_substr(trim($post['description'] ?? ''), 0, 255);

        $errors = [];
        if ($name === '') $errors['name'] = 'Nama metode wajib diisi.';
        if ($number === '') $errors['number'] = 'Nomor rekening/e-wallet wajib diisi.';
        if ($holder === '') $errors['holder'] = 'Nama pemilik wajib diisi.';
        return [$type, $name, $number, $holder, $description, $errors];
    }

    private function codeFrom($type, $name, $number) {
        $code = \slug($name . '-' . $type);
        if ($code === '') $code = $type;
        $code = mb_substr($code, 0, 40);
        if ($number !== '') {
            $candidate = mb_substr(\slug($type . '-' . $number), 0, 40);
            if ($candidate !== '') $code = $candidate;
        }
        return $code;
    }

    public function index() {
        \Auth::requireAdmin();
        $db = \Database::getInstance();
        $methods = $db->fetchAll("SELECT * FROM payment_methods ORDER BY sort_order ASC, id ASC");
        $admin_page_title = 'Metode Pembayaran';
        require __DIR__ . '/../../views/layouts/admin-header.php';
        require __DIR__ . '/../../views/admin/payments/index.php';
        require __DIR__ . '/../../views/layouts/admin-footer.php';
    }

    public function store() {
        \Auth::requireAdmin();
        if (!\verify_csrf()) {
            \flash_set('error', 'Token tidak valid.');
            header('Location: /geprek-geh/admin/payments');
            exit;
        }
        $db = \Database::getInstance();
        [$type, $name, $number, $holder, $description, $errors] = $this->validate($_POST);
        if ($errors) {
            \flash_set('error', implode(' ', $errors));
            header('Location: /geprek-geh/admin/payments');
            exit;
        }
        $code = $this->codeFrom($type, $name, $number);
        while (!$this->isUniqueCode($db, $code)) {
            $code = \slug($code . '-2');
        }
        $next = max(0, (int) $db->fetchColumn("SELECT COALESCE(MAX(sort_order),0) + 1 FROM payment_methods"));
        $db->insert('payment_methods', [
            'code'        => $code,
            'type'        => $type,
            'name'        => $name,
            'number'      => $number,
            'holder'      => $holder,
            'description' => $description ?: null,
            'is_active'   => 1,
            'sort_order'  => $next,
        ]);
        \flash_set('success', "Metode {$name} berhasil ditambahkan.");
        header('Location: /geprek-geh/admin/payments');
        exit;
    }

    public function update($id) {
        \Auth::requireAdmin();
        if (!\verify_csrf()) {
            \flash_set('error', 'Token tidak valid.');
            header('Location: /geprek-geh/admin/payments');
            exit;
        }
        $db = \Database::getInstance();
        $method = $db->fetchOne("SELECT * FROM payment_methods WHERE id = ?", [(int) $id]);
        if (!$method) {
            \flash_set('error', 'Metode tidak ditemukan.');
            header('Location: /geprek-geh/admin/payments');
            exit;
        }
        [$type, $name, $number, $holder, $description, $errors] = $this->validate($_POST);
        if ($errors) {
            \flash_set('error', implode(' ', $errors));
            header('Location: /geprek-geh/admin/payments#m-' . (int) $id);
            exit;
        }
        $code = $this->codeFrom($type, $name, $number);
        if ($code !== $method['code'] && !$this->isUniqueCode($db, $code)) {
            $code .= '-' . substr((string) $method['id'], 0, 3);
        }
        $is_active = isset($_POST['is_active']) ? 1 : (int) $method['is_active'];
        $sort_order = (int) ($_POST['sort_order'] ?? $method['sort_order']);

        $db->update('payment_methods', [
            'code'        => $code,
            'type'        => $type,
            'name'        => $name,
            'number'      => $number,
            'holder'      => $holder,
            'description' => $description ?: null,
            'is_active'   => $is_active,
            'sort_order'  => $sort_order,
        ], 'id = ?', [(int) $id]);

        \flash_set('success', "Metode {$name} berhasil diperbarui.");
        header('Location: /geprek-geh/admin/payments#m-' . (int) $id);
        exit;
    }

    public function toggle($id) {
        \Auth::requireAdmin();
        if (!\verify_csrf()) {
            \flash_set('error', 'Token tidak valid.');
            header('Location: /geprek-geh/admin/payments');
            exit;
        }
        $db = \Database::getInstance();
        $method = $db->fetchOne("SELECT * FROM payment_methods WHERE id = ?", [(int) $id]);
        if (!$method) {
            \flash_set('error', 'Metode tidak ditemukan.');
            header('Location: /geprek-geh/admin/payments');
            exit;
        }
        $db->update('payment_methods', ['is_active' => (int) $method['is_active'] ? 0 : 1], 'id = ?', [(int) $id]);
        \flash_set('success', 'Status metode diperbarui.');
        header('Location: /geprek-geh/admin/payments');
        exit;
    }

    public function delete($id) {
        \Auth::requireAdmin();
        if (!\verify_csrf()) {
            \flash_set('error', 'Token tidak valid.');
            header('Location: /geprek-geh/admin/payments');
            exit;
        }
        $db = \Database::getInstance();
        $method = $db->fetchOne("SELECT * FROM payment_methods WHERE id = ?", [(int) $id]);
        if (!$method) {
            \flash_set('error', 'Metode tidak ditemukan.');
            header('Location: /geprek-geh/admin/payments');
            exit;
        }
        $db->delete('payment_methods', 'id = ?', [(int) $id]);
        \flash_set('success', "Metode {$method['name']} dihapus. Pesanan lama tetap memakai datanya lewat kode lama.");
        header('Location: /geprek-geh/admin/payments');
        exit;
    }
}