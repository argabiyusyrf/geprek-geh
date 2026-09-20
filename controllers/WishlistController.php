<?php
class WishlistController {

    public function index() {
        Auth::requireLogin();
        $db = Database::getInstance();
        $products = $db->fetchAll(
            "SELECT w.id AS wish_id, w.created_at AS wished_at,
                    p.id, p.slug, p.name, p.price, p.stock, p.image,
                    c.name AS category_name,
                    (SELECT COUNT(*) FROM product_reviews r WHERE r.product_id = p.id AND r.is_visible = 1) AS review_count,
                    (SELECT COALESCE(AVG(r.rating),0) FROM product_reviews r WHERE r.product_id = p.id AND r.is_visible = 1) AS avg_rating
               FROM wishlists w
               JOIN products p ON p.id = w.product_id
               LEFT JOIN categories c ON p.category_id = c.id
              WHERE w.user_id = ? AND p.is_active = 1
              ORDER BY w.created_at DESC",
            [Auth::id()]
        );

        $admin_page_title = 'Daftar Keinginan';
        render('wishlist/index', get_defined_vars());
    }

    public function toggle($id) {
        Auth::requireLogin();
        if (!verify_csrf()) {
            flash_set('error', 'Token tidak valid.');
            redirect('/geprek-geh/wishlist');
        }
        $db = Database::getInstance();
        $id = (int) $id;
        $product = $db->fetchOne("SELECT name FROM products WHERE id = ?", [$id]);
        if (!$product) {
            flash_set('error', 'Produk tidak ditemukan.');
            redirect('/geprek-geh/products');
        }
        $exists = $db->fetchOne(
            "SELECT id FROM wishlists WHERE user_id = ? AND product_id = ?",
            [Auth::id(), $id]
        );
        if ($exists) {
            $db->delete('wishlists', 'user_id = ? AND product_id = ?', [Auth::id(), $id]);
            flash_set('info', $product['name'] . ' dihapus dari daftar keinginan.');
        } else {
            $db->insert('wishlists', ['user_id' => Auth::id(), 'product_id' => $id]);
            flash_set('success', $product['name'] . ' ditambahkan ke daftar keinginan.');
        }

        // Kembali ke halaman asal (hanya path lokal, hindari open redirect).
        $back = trim((string) ($_SERVER['HTTP_REFERER'] ?? ''));
        $path = ($back !== '' ? parse_url($back, PHP_URL_PATH) : '') ?: '/geprek-geh/products';
        redirect($path);
    }
}