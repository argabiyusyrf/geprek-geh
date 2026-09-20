<?php
namespace Admin;
class PromoController {
    public function index() {
        \Auth::requireStaff();
        $db = \Database::getInstance();

        $allowed_status = ['active', 'upcoming', 'expired', 'inactive'];
        $status = $_GET['status'] ?? '';
        if (!in_array($status, $allowed_status, true)) $status = '';

        $q = trim((string) ($_GET['q'] ?? ''));
        $sort = $_GET['sort'] ?? 'terbaru';
        $allowed_sort = ['terbaru', 'terlama', 'nama', 'nilai'];
        if (!in_array($sort, $allowed_sort, true)) $sort = 'terbaru';

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $per = (int) ($_GET['per'] ?? 15);
        if (!in_array($per, [10, 15, 25, 50], true)) $per = 15;
        $offset = ($page - 1) * $per;

        $where = '1=1';
        $params = [];
        switch ($status) {
            case 'active':
                $where .= " AND is_active = 1 AND (expires_at IS NULL OR expires_at > NOW()) AND (starts_at IS NULL OR starts_at <= NOW())";
                break;
            case 'upcoming':
                $where .= " AND is_active = 1 AND starts_at IS NOT NULL AND starts_at > NOW()";
                break;
            case 'expired':
                $where .= " AND expires_at IS NOT NULL AND expires_at < NOW()";
                break;
            case 'inactive':
                $where .= " AND is_active = 0 AND (expires_at IS NULL OR expires_at > NOW())";
                break;
        }
        if ($q !== '') {
            $where .= " AND code LIKE ?";
            $params[] = '%' . $q . '%';
        }

        $stats = $db->fetchOne("SELECT
            COUNT(*) AS total,
            COALESCE(SUM(CASE WHEN is_active = 1 AND (expires_at IS NULL OR expires_at > NOW()) AND (starts_at IS NULL OR starts_at <= NOW()) THEN 1 ELSE 0 END), 0) AS aktif,
            COALESCE(SUM(CASE WHEN is_active = 1 AND starts_at IS NOT NULL AND starts_at > NOW() THEN 1 ELSE 0 END), 0) AS akan_tiba,
            COALESCE(SUM(CASE WHEN expires_at IS NOT NULL AND expires_at < NOW() THEN 1 ELSE 0 END), 0) AS berakhir,
            COALESCE(SUM(CASE WHEN is_active = 0 AND (expires_at IS NULL OR expires_at > NOW()) THEN 1 ELSE 0 END), 0) AS nonaktif
         FROM promo_codes");
        $kpis = [
            'total'      => (int) $stats['total'],
            'aktif'      => (int) $stats['aktif'],
            'akan_tiba'  => (int) $stats['akan_tiba'],
            'berakhir'   => (int) $stats['berakhir'],
            'nonaktif'   => (int) $stats['nonaktif'],
        ];

        $total = (int) $db->fetchColumn("SELECT COUNT(*) FROM promo_codes WHERE {$where}", $params);
        $total_pages = max(1, (int) ceil($total / $per));
        if ($page > $total_pages) $page = $total_pages;

        $order_dir = [
            'terbaru' => 'created_at DESC',
            'terlama' => 'created_at ASC',
            'nama'    => 'code ASC',
            'nilai'   => 'value DESC',
        ];
        $sort_sql = $order_dir[$sort];

        $promos = $db->fetchAll(
            "SELECT * FROM promo_codes WHERE {$where} ORDER BY {$sort_sql} LIMIT {$per} OFFSET {$offset}",
            $params
        );

        $formErrors = \form_errors();
        $formOld    = \form_old();

        render('admin/promos/index', get_defined_vars());
    }

    private function collectInput(): array {
        return [
            'code'       => strtoupper(trim($_POST['code'] ?? '')),
            'type'       => ($_POST['type'] ?? 'percentage') === 'fixed' ? 'fixed' : 'percentage',
            'value'      => (float) ($_POST['value'] ?? 0),
            'min_order'  => max(0, (int) ($_POST['min_order'] ?? 0)),
            'max_uses'   => max(0, (int) ($_POST['max_uses'] ?? 0)),
            'starts_at'  => trim($_POST['starts_at'] ?? '') ?: null,
            'expires_at' => trim($_POST['expires_at'] ?? '') ?: null,
            'is_active'  => isset($_POST['is_active']) ? 1 : 0,
        ];
    }

    private function normalizeDatetime($v): ?string {
        if (!$v) return null;
        $d = new \DateTime($v);
        return $d->format('Y-m-d H:i:s');
    }

    private function validate(array $in, ?int $ignoreId = null): array {
        $errors = [];
        $db = \Database::getInstance();
        if ($in['code'] === '') {
            $errors['code'] = 'Kode promo wajib diisi.';
        } elseif ($db->fetchOne("SELECT id FROM promo_codes WHERE code = ? AND id != ?", [$in['code'], (int) ($ignoreId ?? 0)])) {
            $errors['code'] = 'Kode "' . $in['code'] . '" sudah dipakai.';
        }
        if ($in['value'] <= 0) {
            $errors['value'] = 'Nilai diskon harus lebih dari 0.';
        } elseif ($in['type'] === 'percentage' && $in['value'] > 100) {
            $errors['value'] = 'Persentase maksimal 100%.';
        }
        if ($in['starts_at'] && $in['expires_at'] && strtotime($in['expires_at']) < strtotime($in['starts_at'])) {
            $errors['expires_at'] = 'Kedaluwarsa harus setelah tanggal mulai.';
        }
        return $errors;
    }

    public function store() {
        \Auth::requireStaff();
        if (!\verify_csrf()) { \flash_set('error', 'Token tidak valid.'); header('Location: /geprek-geh/admin/promos'); exit; }
        $db = \Database::getInstance();
        $in = $this->collectInput();

        $errors = $this->validate($in);
        if ($errors) {
            \form_stash($errors, $in);
            \flash_set('error', 'Mohon periksa kembali isian form promo.');
            header('Location: /geprek-geh/admin/promos?create=1&error=1'); exit;
        }

        $db->insert('promo_codes', [
            'code'       => $in['code'],
            'type'       => $in['type'],
            'value'      => $in['value'],
            'min_order'  => $in['min_order'],
            'max_uses'   => $in['max_uses'] ?: null,
            'used_count' => 0,
            'starts_at'  => $this->normalizeDatetime($in['starts_at']),
            'expires_at' => $this->normalizeDatetime($in['expires_at']),
            'is_active'  => $in['is_active'],
        ]);

        \flash_set('success', 'Kode promo "' . $in['code'] . '" berhasil dibuat.');
        header('Location: /geprek-geh/admin/promos');
        exit;
    }

    public function update($id) {
        \Auth::requireStaff();
        $id = (int) $id;
        if (!\verify_csrf()) { \flash_set('error', 'Token tidak valid.'); header('Location: /geprek-geh/admin/promos'); exit; }
        $db = \Database::getInstance();
        $promo = $db->fetchOne("SELECT * FROM promo_codes WHERE id = ?", [$id]);
        if (!$promo) {
            \flash_set('error', 'Kode promo tidak ditemukan.');
            header('Location: /geprek-geh/admin/promos'); exit;
        }
        $in = $this->collectInput();

        $errors = $this->validate($in, $id);
        if ($errors) {
            \form_stash($errors, $in);
            \flash_set('error', 'Mohon periksa kembali isian form promo.');
            header('Location: /geprek-geh/admin/promos?edit=' . $id . '&error=1'); exit;
        }

        $db->update('promo_codes', [
            'code'       => $in['code'],
            'type'       => $in['type'],
            'value'      => $in['value'],
            'min_order'  => $in['min_order'],
            'max_uses'   => $in['max_uses'] ?: null,
            'starts_at'  => $this->normalizeDatetime($in['starts_at']),
            'expires_at' => $this->normalizeDatetime($in['expires_at']),
            'is_active'  => $in['is_active'],
        ], 'id = ?', [$id]);

        \flash_set('success', 'Kode promo "' . $in['code'] . '" berhasil diupdate.');
        header('Location: /geprek-geh/admin/promos');
        exit;
    }

    public function toggle($id) {
        \Auth::requireStaff();
        $db = \Database::getInstance();
        $promo = $db->fetchOne("SELECT * FROM promo_codes WHERE id = ?", [$id]);
        if (!$promo) {
            \flash_set('error', 'Kode promo tidak ditemukan.');
            header('Location: /geprek-geh/admin/promos');
            exit;
        }
        $db->update('promo_codes', ['is_active' => $promo['is_active'] ? 0 : 1], 'id = ?', [$id]);
        \flash_set('success', 'Status kode promo "' . $promo['code'] . '" diperbarui.');
        header('Location: /geprek-geh/admin/promos');
        exit;
    }

    public function delete($id) {
        \Auth::requireStaff();
        $db = \Database::getInstance();
        $db->delete('promo_codes', 'id = ?', [(int) $id]);
        \flash_set('success', 'Kode promo dihapus.');
        header('Location: /geprek-geh/admin/promos');
        exit;
    }
}