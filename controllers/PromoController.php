<?php
class PromoController {
    public function apply() {
        Auth::requireLogin();
        if (!verify_csrf()) {
            flash_set('error', 'Token tidak valid.');
            redirect('/geprek-geh/cart');
        }

        $code = strtoupper(trim($_POST['promo_code'] ?? ''));
        if ($code === '') {
            flash_set('error', 'Masukkan kode promo.');
            redirect('/geprek-geh/cart');
        }

        $db = Database::getInstance();
        $promo = $db->fetchOne(
            "SELECT * FROM promo_codes WHERE code = ? AND is_active = 1",
            [$code]
        );

        if (!$promo) {
            flash_set('error', 'Kode promo tidak valid.');
            redirect('/geprek-geh/cart');
        }

        if ($promo['starts_at'] && strtotime($promo['starts_at']) > time()) {
            flash_set('error', 'Kode promo belum berlaku.');
            redirect('/geprek-geh/cart');
        }
        if ($promo['expires_at'] && strtotime($promo['expires_at']) < time()) {
            flash_set('error', 'Kode promo sudah kedaluwarsa.');
            redirect('/geprek-geh/cart');
        }
        if ($promo['max_uses'] !== null && $promo['used_count'] >= $promo['max_uses']) {
            flash_set('error', 'Kode promo sudah mencapai batas penggunaan.');
            redirect('/geprek-geh/cart');
        }

        $cart = cart_summary();
        if ($cart['subtotal'] < $promo['min_order']) {
            flash_set('error', 'Minimal belanja ' . rupiah($promo['min_order']) . ' untuk kode ini.');
            redirect('/geprek-geh/cart');
        }

        $_SESSION['promo'] = [
            'id'        => $promo['id'],
            'code'      => $promo['code'],
            'type'      => $promo['type'],
            'value'     => $promo['value'],
        ];

        flash_set('success', 'Kode promo "' . $promo['code'] . '" berhasil diterapkan!');
        redirect('/geprek-geh/cart');
    }

    public function remove() {
        Auth::requireLogin();
        if (!verify_csrf()) {
            flash_set('error', 'Token tidak valid.');
            redirect('/geprek-geh/cart');
        }
        unset($_SESSION['promo']);
        flash_set('success', 'Kode promo dihapus.');
        redirect('/geprek-geh/cart');
    }

    /** Calculate discount from session promo. Returns ['discount' => int, 'label' => string]. */
    public static function calcDiscount(array $promo, int $subtotal): array {
        if ($promo['type'] === 'percentage') {
            $discount = (int) ($subtotal * $promo['value'] / 100);
        } else {
            $discount = min((int) $promo['value'], $subtotal);
        }
        $label = $promo['type'] === 'percentage'
            ? $promo['value'] . '%'
            : rupiah($promo['value']);
        return ['discount' => $discount, 'label' => $label];
    }

    /** Admin: list all promo codes */
    public static function all(): array {
        $db = Database::getInstance();
        return $db->fetchAll("SELECT * FROM promo_codes ORDER BY created_at DESC");
    }

    /** Admin: create promo code */
    public static function create(array $data): int {
        $db = Database::getInstance();
        return $db->insert('promo_codes', $data);
    }

    /** Admin: delete promo code */
    public static function delete(int $id): void {
        $db = Database::getInstance();
        $db->delete('promo_codes', 'id = ?', [$id]);
    }
}
