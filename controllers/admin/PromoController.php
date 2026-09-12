<?php
namespace Admin;
class PromoController {
    public function index() {
        \Auth::requireAdmin();
        $db = \Database::getInstance();
        $promos = $db->fetchAll("SELECT * FROM promo_codes ORDER BY created_at DESC");

        $formErrors = \form_errors();
        $formOld    = \form_old();

        require __DIR__ . '/../../views/layouts/admin-header.php';
        require __DIR__ . '/../../views/admin/promos/index.php';
        require __DIR__ . '/../../views/layouts/admin-footer.php';
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
        \Auth::requireAdmin();
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
        \Auth::requireAdmin();
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
        \Auth::requireAdmin();
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
        \Auth::requireAdmin();
        $db = \Database::getInstance();
        $db->delete('promo_codes', 'id = ?', [(int) $id]);
        \flash_set('success', 'Kode promo dihapus.');
        header('Location: /geprek-geh/admin/promos');
        exit;
    }
}