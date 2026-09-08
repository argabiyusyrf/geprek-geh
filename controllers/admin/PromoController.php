<?php
namespace Admin;
class PromoController {
    public function index() {
        \Auth::requireAdmin();
        $db = \Database::getInstance();
        $promos = $db->fetchAll(
            "SELECT * FROM promo_codes ORDER BY created_at DESC"
        );
        require __DIR__ . '/../../views/layouts/admin-header.php';
        require __DIR__ . '/../../views/admin/promos/index.php';
        require __DIR__ . '/../../views/layouts/admin-footer.php';
    }

    public function store() {
        \Auth::requireAdmin();
        $db = \Database::getInstance();
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $type = $_POST['type'] ?? 'percentage';
        $value = (float)($_POST['value'] ?? 0);
        $min_order = (int)($_POST['min_order'] ?? 0);
        $max_uses = (int)($_POST['max_uses'] ?? 0) ?: null;
        $starts_at = $_POST['starts_at'] ?? null;
        $expires_at = $_POST['expires_at'] ?? null;

        if ($code === '') {
            \flash_set('error', 'Kode promo wajib diisi.');
            header('Location: /geprek-geh/admin/promos');
            exit;
        }

        $db->insert('promo_codes', [
            'code'      => $code,
            'type'      => $type,
            'value'     => $value,
            'min_order' => $min_order,
            'max_uses'  => $max_uses,
            'used_count'=> 0,
            'starts_at' => $starts_at ?: null,
            'expires_at'=> $expires_at ?: null,
            'is_active' => 1,
            'created_at'=> date('Y-m-d H:i:s'),
        ]);

        \flash_set('success', 'Kode promo "' . $code . '" berhasil dibuat.');
        header('Location: /geprek-geh/admin/promos');
        exit;
    }

    public function toggle($id) {
        \Auth::requireAdmin();
        $db = \Database::getInstance();
        $promo = $db->fetchOne("SELECT * FROM promo_codes WHERE id = ?", [$id]);
        if (!$promo) {
            \flash_set('error', 'Kode promo tidak ditemukan.');
            header('Location: /geprek-geh/admin/promos');
            exit;
        }
        $db->update('promo_codes', ['is_active' => !$promo['is_active']], 'id = ?', [$id]);
        \flash_set('success', 'Status kode promo diperbarui.');
        header('Location: /geprek-geh/admin/promos');
        exit;
    }

    public function delete($id) {
        \Auth::requireAdmin();
        $db = \Database::getInstance();
        $db->delete('promo_codes', 'id = ?', [(int)$id]);
        \flash_set('success', 'Kode promo dihapus.');
        header('Location: /geprek-geh/admin/promos');
        exit;
    }
}
