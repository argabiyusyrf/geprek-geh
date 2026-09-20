<?php
class ReviewController {
    public function store() {
        Auth::requireLogin();
        if (!verify_csrf()) {
            flash_set('error', 'Token tidak valid.');
            redirect('/products');
        }

        $product_id = (int)($_POST['product_id'] ?? 0);
        $rating = (int)($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');

        if ($product_id < 1 || $rating < 1 || $rating > 5) {
            flash_set('error', 'Rating tidak valid.');
            redirect('/products');
        }

        $db = Database::getInstance();
        $product = $db->fetchOne("SELECT id, slug FROM products WHERE id = ? AND is_active = 1", [$product_id]);
        if (!$product) {
            flash_set('error', 'Produk tidak ditemukan.');
            redirect('/products');
        }

        $existing = $db->fetchOne(
            "SELECT id FROM product_reviews WHERE product_id = ? AND user_id = ?",
            [$product_id, Auth::id()]
        );

        if (!$existing) {
            $has_delivered = $db->fetchColumn(
                "SELECT COUNT(*) FROM orders o
                 JOIN order_items oi ON oi.order_id = o.id
                 WHERE o.user_id = ? AND o.status = 'delivered' AND oi.product_id = ?",
                [Auth::id(), $product_id]
            );
            if (!$has_delivered) {
                flash_set('error', 'Hanya pembeli terverifikasi yang bisa memberi ulasan.');
                redirect('/products/' . $product['slug']);
            }
        }

        if ($existing) {
            $fields = [
                'rating'   => $rating,
                'comment'  => $comment,
            ];
            $photo = $this->handlePhotoUpload();
            if ($photo) {
                $this->deletePhotoFile($existing['image'] ?? null);
                $fields['image'] = $photo;
            } elseif ($photo === false) {
                // upload gagal validasi → flash sudah terisi
                redirect('/products/' . $product['slug']);
            }
            $db->update('product_reviews', $fields, 'id = ?', [$existing['id']]);
            flash_set('success', 'Review berhasil diperbarui.');
        } else {
            $photo = $this->handlePhotoUpload();
            if ($photo === false) {
                redirect('/products/' . $product['slug']);
            }
            $db->insert('product_reviews', [
                'product_id' => $product_id,
                'user_id'    => Auth::id(),
                'rating'     => $rating,
                'comment'    => $comment,
                'image'      => $photo ?: null,
            ]);
            flash_set('success', 'Review berhasil ditambahkan. Terima kasih!');
        }

        redirect('/products/' . $product['slug']);
    }

    /** Upload foto ulasan opsional. Return nama file, null tanpa file, atau false bila gagal validasi. */
    private function handlePhotoUpload() {
        if (!isset($_FILES['photo']) || $_FILES['photo']['error'] === UPLOAD_ERR_NO_FILE) return null;
        if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) return false;
        $file = $_FILES['photo'];
        if ($file['size'] > 5 * 1024 * 1024) {
            flash_set('error', 'Ukuran foto maksimal 5MB.');
            return false;
        }
        $imageInfo = @getimagesize($file['tmp_name']);
        $typeToExt = [IMAGETYPE_PNG => 'png', IMAGETYPE_JPEG => 'jpg', IMAGETYPE_WEBP => 'webp'];
        if (!$imageInfo || !isset($typeToExt[$imageInfo[2]])) {
            flash_set('error', 'File foto tidak valid (PNG/JPG/WebP saja).');
            return false;
        }
        $ext = $typeToExt[$imageInfo[2]];
        $name = 'review_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $upload_dir = __DIR__ . '/../assets/uploads/reviews/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        move_uploaded_file($file['tmp_name'], $upload_dir . $name);
        return $name;
    }

    private function deletePhotoFile($image) {
        if (!$image) return;
        $path = __DIR__ . '/../assets/uploads/reviews/' . basename((string) $image);
        if (is_file($path)) @unlink($path);
    }

    public function delete($id) {
        Auth::requireLogin();
        if (!verify_csrf()) {
            flash_set('error', 'Token tidak valid.');
            redirect('/products');
        }

        $db = Database::getInstance();
        $review = $db->fetchOne(
            "SELECT pr.*, p.slug FROM product_reviews pr JOIN products p ON pr.product_id = p.id WHERE pr.id = ? AND pr.user_id = ?",
            [$id, Auth::id()]
        );
        if (!$review) {
            flash_set('error', 'Review tidak ditemukan.');
            redirect('/products');
        }

        $this->deletePhotoFile($review['image'] ?? null);
        $db->delete('product_reviews', 'id = ?', [$id]);
        flash_set('success', 'Review berhasil dihapus.');
        redirect('/products/' . $review['slug']);
    }
}
