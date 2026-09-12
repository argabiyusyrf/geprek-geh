<?php
namespace Admin;
class ProductController {
    public function index() {
        \Auth::requireAdmin();
        $db = \Database::getInstance();
        $categories = $db->fetchAll(
            "SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS product_count
               FROM categories c ORDER BY c.name");

        // ── Cari / filter / urutkan (whitelisted) ──
        $q        = trim((string) ($_GET['q'] ?? ''));
        $category = max(0, (int) ($_GET['category'] ?? 0));
        $status   = $_GET['status'] ?? '';
        if (!in_array($status, ['active', 'inactive', 'featured'], true)) $status = '';

        $sorts = [
            'newest'     => 'p.created_at DESC, p.id DESC',
            'oldest'     => 'p.created_at ASC, p.id ASC',
            'name_asc'   => 'p.name ASC',
            'name_desc'  => 'p.name DESC',
            'price_asc'  => 'p.price ASC',
            'price_desc' => 'p.price DESC',
            'stock_low'  => 'p.stock ASC, p.id DESC',
            'stock_high' => 'p.stock DESC',
        ];
        $sort = $_GET['sort'] ?? 'newest';
        if (!isset($sorts[$sort])) $sort = 'newest';
        $order_sql = $sorts[$sort];

        $where   = '1=1';
        $params  = [];
        if ($q !== '') {
            $where  .= ' AND (p.name LIKE ? OR p.slug LIKE ?)';
            $params[] = "%{$q}%";
            $params[] = "%{$q}%";
        }
        if ($category > 0) {
            $where  .= ' AND p.category_id = ?';
            $params[] = $category;
        }
        if ($status === 'active')   $where .= ' AND p.is_active = 1';
        if ($status === 'inactive') $where .= ' AND p.is_active = 0';
        if ($status === 'featured') $where .= ' AND p.is_featured = 1';

        // ── Paginasi ──
        $per_page   = 10;
        $page       = max(1, (int) ($_GET['page'] ?? 1));
        $total      = (int) $db->fetchColumn("SELECT COUNT(*) FROM products p WHERE {$where}", $params);
        $total_pages = max(1, ceil($total / $per_page));
        if ($page > $total_pages) $page = $total_pages;
        $offset = ($page - 1) * $per_page;

        $products = $db->fetchAll(
            "SELECT p.*, c.name AS category_name
               FROM products p JOIN categories c ON p.category_id = c.id
              WHERE {$where} ORDER BY {$order_sql} LIMIT {$per_page} OFFSET {$offset}",
            $params
        );

        // Ringkasan untuk tab status & stok menipis (tanpa filter status tapi ikut q/kategori)
        $sWhere  = '1=1';
        $sParams = [];
        if ($q !== '') {
            $sWhere  .= ' AND (p.name LIKE ? OR p.slug LIKE ?)';
            $sParams[] = "%{$q}%";
            $sParams[] = "%{$q}%";
        }
        if ($category > 0) {
            $sWhere  .= ' AND p.category_id = ?';
            $sParams[] = $category;
        }
        $status_row    = $db->fetchOne(
            "SELECT SUM(p.is_active = 1) AS active, SUM(p.is_active = 0) AS inactive,
                    SUM(p.is_featured = 1) AS featured FROM products p WHERE {$sWhere}", $sParams);
        $status_counts = [
            'active'   => (int) ($status_row['active'] ?? 0),
            'inactive' => (int) ($status_row['inactive'] ?? 0),
            'featured' => (int) ($status_row['featured'] ?? 0),
        ];
        $lowStockCount = (int) $db->fetchColumn(
            "SELECT COUNT(*) FROM products p WHERE {$sWhere} AND p.is_active = 1 AND p.stock <= 5", $sParams);

        // ── Produk terlaris (penjualan terbanyak) ──
        $top_sellers = [];
        foreach ($db->fetchAll(
            "SELECT oi.product_id, p.name, p.image, COALESCE(c.name, '') AS category_name,
                    SUM(oi.quantity) AS qty, SUM(oi.quantity * oi.price) AS revenue
               FROM order_items oi
               JOIN orders o        ON oi.order_id = o.id
               JOIN products p      ON oi.product_id = p.id
               LEFT JOIN categories c ON p.category_id = c.id
              WHERE o.status != 'cancelled'
              GROUP BY oi.product_id, p.name, p.image, c.name
              ORDER BY qty DESC, revenue DESC
              LIMIT 5"
        ) as $t) {
            $t['qty']     = (int) $t['qty'];
            $t['revenue'] = (int) $t['revenue'];
            $top_sellers[] = $t;
        }
        $top_max   = $top_sellers ? max(array_column($top_sellers, 'qty')) : 0;
        $top_total = array_sum(array_column($top_sellers, 'qty'));

        // ── Stok menipis (panel "perlu perhatian") ──
        $low_stock_items = $db->fetchAll(
            "SELECT p.id, p.name, p.image, p.stock, c.name AS category_name
               FROM products p JOIN categories c ON p.category_id = c.id
              WHERE p.is_active = 1 AND p.stock <= 5
              ORDER BY p.stock ASC, p.name ASC LIMIT 5");

        // Query params aktif untuk dipakai ulang di link filter/pagination
        $filter = ['q' => $q];
        if ($category > 0) $filter['category'] = $category;
        if ($status !== '') $filter['status'] = $status;
        if ($sort !== 'newest') $filter['sort'] = $sort;
        $filter_active = ($q !== '' || $category > 0 || $status !== '' || $sort !== 'newest');

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
        $price = max(0, (int) preg_replace('/[^0-9]/', '', (string) ($_POST['price'] ?? '')));
        $stock = max(0, (int) preg_replace('/[^0-9]/', '', (string) ($_POST['stock'] ?? '')));
        $description = \sanitize_rich_text($_POST['description'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;

        $stash = [
            'name' => $name, 'category_id' => $category_id, 'price' => $price,
            'stock' => $stock, 'description' => $description,
            'is_active' => $is_active, 'is_featured' => $is_featured,
        ];

        if ($name === '') {
            \form_stash(['name' => 'Nama produk tidak boleh kosong.'], $stash);
            \flash_set('error', 'Mohon periksa kembali isian form produk.');
            header('Location: /geprek-geh/admin/products?create=1&error=1'); exit;
        }
        if (!$db->fetchOne("SELECT id FROM categories WHERE id = ?", [$category_id])) {
            \form_stash(['category_id' => 'Kategori tidak valid.'], $stash);
            \flash_set('error', 'Mohon periksa kembali isian form produk.');
            header('Location: /geprek-geh/admin/products?create=1&error=1'); exit;
        }
        $image = $this->validImageUpload();
        if ($image === false) {
            \form_stash([], $stash);
            header('Location: /geprek-geh/admin/products?create=1&error=1'); exit;
        }

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
        $price = max(0, (int) preg_replace('/[^0-9]/', '', (string) ($_POST['price'] ?? '')));
        $stock = max(0, (int) preg_replace('/[^0-9]/', '', (string) ($_POST['stock'] ?? '')));
        $description = \sanitize_rich_text($_POST['description'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;

        $stash = [
            'name' => $name, 'category_id' => $category_id, 'price' => $price,
            'stock' => $stock, 'description' => $description,
            'is_active' => $is_active, 'is_featured' => $is_featured,
        ];

        if ($name === '') {
            \form_stash(['name' => 'Nama produk tidak boleh kosong.'], $stash);
            \flash_set('error', 'Mohon periksa kembali isian form produk.');
            header('Location: /geprek-geh/admin/products?edit=' . $id . '&error=1'); exit;
        }
        if (!$db->fetchOne("SELECT id FROM categories WHERE id = ?", [$category_id])) {
            \form_stash(['category_id' => 'Kategori tidak valid.'], $stash);
            \flash_set('error', 'Mohon periksa kembali isian form produk.');
            header('Location: /geprek-geh/admin/products?edit=' . $id . '&error=1'); exit;
        }
        $image = $this->validImageUpload();
        if ($image === false) {
            \form_stash([], $stash);
            header('Location: /geprek-geh/admin/products?edit=' . $id . '&error=1'); exit;
        }

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
