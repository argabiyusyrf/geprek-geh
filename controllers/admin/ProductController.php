<?php
namespace Admin;
class ProductController {
    public function index() {
        \Auth::requireAdmin();
        $db = \Database::getInstance();
        $products = $db->fetchAll(
            "SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC"
        );
        $categories = $db->fetchAll("SELECT * FROM categories ORDER BY name");

        $lowStockCount = 0;
        foreach ($products as $p) {
            if ($p['is_active'] && (int) $p['stock'] <= 5) $lowStockCount++;
        }

        $formErrors = \form_errors();
        $formOld    = \form_old();

        require __DIR__ . '/../../views/layouts/admin-header.php';
        require __DIR__ . '/../../views/admin/products/index.php';
        require __DIR__ . '/../../views/layouts/admin-footer.php';
    }

    public function create() {
        \Auth::requireAdmin();
        header('Location: /geprek-geh/admin/products?create=1');
        exit;
    }

    public function store() {
        \Auth::requireAdmin();
        if (!\verify_csrf()) { \flash_set('error', 'Token tidak valid.'); header('Location: /geprek-geh/admin/products?create=1'); exit; }
        $db = \Database::getInstance();
        $name = trim($_POST['name'] ?? '');
        $category_id = (int)($_POST['category_id'] ?? 0);
        $price = max(0, (int)($_POST['price'] ?? 0));
        $stock = max(0, (int)($_POST['stock'] ?? 0));
        $description = trim($_POST['description'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;

        if ($name === '') {
            \form_stash(['name' => 'Nama produk tidak boleh kosong.'], [
                'name' => $name, 'category_id' => $category_id, 'price' => $price,
                'stock' => $stock, 'description' => $description,
                'is_active' => $is_active, 'is_featured' => $is_featured,
            ]);
            \flash_set('error', 'Mohon periksa kembali isian form produk.');
            header('Location: /geprek-geh/admin/products?create=1&error=1'); exit;
        }
        if (!$db->fetchOne("SELECT id FROM categories WHERE id = ?", [$category_id])) {
            \form_stash(['category_id' => 'Kategori tidak valid.'], [
                'name' => $name, 'category_id' => $category_id, 'price' => $price,
                'stock' => $stock, 'description' => $description,
                'is_active' => $is_active, 'is_featured' => $is_featured,
            ]);
            \flash_set('error', 'Mohon periksa kembali isian form produk.');
            header('Location: /geprek-geh/admin/products?create=1&error=1'); exit;
        }
        $image = $this->validImageUpload();
        if ($image === false) { header('Location: /geprek-geh/admin/products?create=1&error=1'); exit; }

        $db->insert('products', [
            'name'        => $name,
            'slug'        => $this->uniqueSlug(\slug($name)),
            'category_id' => $category_id,
            'price'       => $price,
            'stock'       => $stock,
            'description' => $description,
            'image'       => $image,
            'is_active'   => $is_active,
            'is_featured' => $is_featured,
        ]);

        \flash_set('success', 'Produk berhasil ditambahkan.');
        header('Location: /geprek-geh/admin/products');
        exit;
    }

    public function edit($id) {
        \Auth::requireAdmin();
        $db = \Database::getInstance();
        $product = $db->fetchOne("SELECT id FROM products WHERE id = ?", [$id]);
        if (!$product) {
            \flash_set('error', 'Produk tidak ditemukan.');
            header('Location: /geprek-geh/admin/products');
            exit;
        }
        header('Location: /geprek-geh/admin/products?edit=' . (int) $id);
        exit;
    }

    private function uniqueSlug(string $base, int $ignoreId = 0): string {
        $db = \Database::getInstance();
        $slug = $base;
        $n = 2;
        while ($db->fetchOne("SELECT id FROM products WHERE slug = ? AND id != ?", [$slug, $ignoreId])) {
            $slug = $base . '-' . $n++;
        }
        return $slug;
    }

    private function validImageUpload(): ?string {
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) return null;
        $file = $_FILES['image'];
        if ($file['size'] > 5 * 1024 * 1024) {
            flash_set('error', 'Ukuran gambar maksimal 5MB.');
            return false;
        }
        $imageInfo = @getimagesize($file['tmp_name']);
        $typeToExt = [IMAGETYPE_PNG => 'png', IMAGETYPE_JPEG => 'jpg', IMAGETYPE_WEBP => 'webp'];
        if (!$imageInfo || !isset($typeToExt[$imageInfo[2]])) {
            flash_set('error', 'File gambar tidak valid (PNG/JPG/WebP saja).');
            return false;
        }
        $ext = $typeToExt[$imageInfo[2]];
        $image = 'product_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $upload_dir = __DIR__ . '/../../assets/uploads/products/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        move_uploaded_file($file['tmp_name'], $upload_dir . $image);
        return $image;
    }

    public function update($id) {
        \Auth::requireAdmin();
        $id = (int) $id;
        if (!\verify_csrf()) { \flash_set('error', 'Token tidak valid.'); header('Location: /geprek-geh/admin/products'); exit; }
        $db = \Database::getInstance();
        if (!$db->fetchOne("SELECT id FROM products WHERE id = ?", [$id])) {
            \flash_set('error', 'Produk tidak ditemukan.');
            header('Location: /geprek-geh/admin/products');
            exit;
        }
        $name = trim($_POST['name'] ?? '');
        $category_id = (int)($_POST['category_id'] ?? 0);
        $price = max(0, (int)($_POST['price'] ?? 0));
        $stock = max(0, (int)($_POST['stock'] ?? 0));
        $description = trim($_POST['description'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;

        if ($name === '') {
            \flash_set('error', 'Nama produk tidak boleh kosong.');
            header('Location: /geprek-geh/admin/products?edit=' . $id); exit;
        }
        if (!$db->fetchOne("SELECT id FROM categories WHERE id = ?", [$category_id])) {
            \flash_set('error', 'Kategori tidak valid.');
            header('Location: /geprek-geh/admin/products?edit=' . $id); exit;
        }
        $image = $this->validImageUpload();
        if ($image === false) { header('Location: /geprek-geh/admin/products?edit=' . $id); exit; }

        $data = [
            'name'        => $name,
            'slug'        => $this->uniqueSlug(\slug($name), $id),
            'category_id' => $category_id,
            'price'       => $price,
            'stock'       => $stock,
            'description' => $description,
            'is_active'   => $is_active,
            'is_featured' => $is_featured,
        ];
        if ($image !== null) $data['image'] = $image;

        $db->update('products', $data, 'id = ?', [$id]);
        \flash_set('success', 'Produk berhasil diupdate.');
        header('Location: /geprek-geh/admin/products');
        exit;
    }

    public function delete($id) {
        \Auth::requireAdmin();
        if (!\verify_csrf()) { \flash_set('error', 'Token tidak valid.'); header('Location: /geprek-geh/admin/products'); exit; }
        $db = \Database::getInstance();
        $product = $db->fetchOne("SELECT image FROM products WHERE id = ?", [$id]);
        $db->delete('products', 'id = ?', [$id]);
        if ($product && $product['image']) {
            $file = __DIR__ . '/../../assets/uploads/products/' . $product['image'];
            if (is_file($file)) @unlink($file);
        }
        \flash_set('success', 'Produk berhasil dihapus.');
        header('Location: /geprek-geh/admin/products');
        exit;
    }
}
