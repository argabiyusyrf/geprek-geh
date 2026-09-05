<?php
namespace Admin;
class ProductController {
    public function index() {
        \Auth::requireAdmin();
        $db = \Database::getInstance();
        $products = $db->fetchAll(
            "SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC"
        );

        require __DIR__ . '/../../views/layouts/admin-header.php';
        require __DIR__ . '/../../views/admin/products/index.php';
        require __DIR__ . '/../../views/layouts/admin-footer.php';
    }

    public function create() {
        \Auth::requireAdmin();
        $db = \Database::getInstance();
        $categories = $db->fetchAll("SELECT * FROM categories ORDER BY name");

        require __DIR__ . '/../../views/layouts/admin-header.php';
        require __DIR__ . '/../../views/admin/products/create.php';
        require __DIR__ . '/../../views/layouts/admin-footer.php';
    }

    public function store() {
        \Auth::requireAdmin();
        $db = \Database::getInstance();
        $name = trim($_POST['name'] ?? '');
        $slug = \slug($name);
        $category_id = (int)($_POST['category_id'] ?? 0);
        $price = (int)($_POST['price'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;

        $image = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image = 'product_' . time() . '.' . $ext;
            $upload_dir = __DIR__ . '/../../assets/uploads/products/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image);
        }

        $db->insert('products', [
            'name'        => $name,
            'slug'        => $slug,
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
        $product = $db->fetchOne("SELECT * FROM products WHERE id = ?", [$id]);
        if (!$product) {
            \flash_set('error', 'Produk tidak ditemukan.');
            header('Location: /geprek-geh/admin/products');
            exit;
        }
        $categories = $db->fetchAll("SELECT * FROM categories ORDER BY name");

        require __DIR__ . '/../../views/layouts/admin-header.php';
        require __DIR__ . '/../../views/admin/products/edit.php';
        require __DIR__ . '/../../views/layouts/admin-footer.php';
    }

    public function update($id) {
        \Auth::requireAdmin();
        $db = \Database::getInstance();
        $name = trim($_POST['name'] ?? '');
        $category_id = (int)($_POST['category_id'] ?? 0);
        $price = (int)($_POST['price'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;

        $data = [
            'name'        => $name,
            'slug'        => \slug($name),
            'category_id' => $category_id,
            'price'       => $price,
            'stock'       => $stock,
            'description' => $description,
            'is_active'   => $is_active,
            'is_featured' => $is_featured,
        ];

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image = 'product_' . time() . '.' . $ext;
            $upload_dir = __DIR__ . '/../../assets/uploads/products/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image);
            $data['image'] = $image;
        }

        $db->update('products', $data, 'id = ?', [$id]);
        \flash_set('success', 'Produk berhasil diupdate.');
        header('Location: /geprek-geh/admin/products');
        exit;
    }

    public function delete($id) {
        \Auth::requireAdmin();
        $db = \Database::getInstance();
        $db->delete('products', 'id = ?', [$id]);
        \flash_set('success', 'Produk berhasil dihapus.');
        header('Location: /geprek-geh/admin/products');
        exit;
    }
}
