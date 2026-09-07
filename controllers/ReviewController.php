<?php
class ReviewController {
    public function store() {
        Auth::requireLogin();
        if (!verify_csrf()) {
            flash_set('error', 'Token tidak valid.');
            redirect('/geprek-geh/products');
        }

        $product_id = (int)($_POST['product_id'] ?? 0);
        $rating = (int)($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');

        if ($product_id < 1 || $rating < 1 || $rating > 5) {
            flash_set('error', 'Rating tidak valid.');
            redirect('/geprek-geh/products');
        }

        $db = Database::getInstance();
        $product = $db->fetchOne("SELECT id, slug FROM products WHERE id = ? AND is_active = 1", [$product_id]);
        if (!$product) {
            flash_set('error', 'Produk tidak ditemukan.');
            redirect('/geprek-geh/products');
        }

        $existing = $db->fetchOne(
            "SELECT id FROM product_reviews WHERE product_id = ? AND user_id = ?",
            [$product_id, Auth::id()]
        );

        if ($existing) {
            $db->update('product_reviews', [
                'rating'   => $rating,
                'comment'  => $comment,
            ], 'id = ?', [$existing['id']]);
            flash_set('success', 'Review berhasil diperbarui.');
        } else {
            $db->insert('product_reviews', [
                'product_id' => $product_id,
                'user_id'    => Auth::id(),
                'rating'     => $rating,
                'comment'    => $comment,
            ]);
            flash_set('success', 'Review berhasil ditambahkan. Terima kasih!');
        }

        redirect('/geprek-geh/products/' . $product['slug']);
    }

    public function delete($id) {
        Auth::requireLogin();
        if (!verify_csrf()) {
            flash_set('error', 'Token tidak valid.');
            redirect('/geprek-geh/products');
        }

        $db = Database::getInstance();
        $review = $db->fetchOne(
            "SELECT pr.*, p.slug FROM product_reviews pr JOIN products p ON pr.product_id = p.id WHERE pr.id = ? AND pr.user_id = ?",
            [$id, Auth::id()]
        );
        if (!$review) {
            flash_set('error', 'Review tidak ditemukan.');
            redirect('/geprek-geh/products');
        }

        $db->delete('product_reviews', 'id = ?', [$id]);
        flash_set('success', 'Review berhasil dihapus.');
        redirect('/geprek-geh/products/' . $review['slug']);
    }
}
