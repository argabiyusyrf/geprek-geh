<?php
namespace Admin;
class CategoryController {
    public function index() {
        \Auth::requireAdmin();
        $db = \Database::getInstance();
        $categories = $db->fetchAll(
            "SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id) AS product_count
             FROM categories c ORDER BY c.sort_order, c.name"
        );

        require __DIR__ . '/../../views/layouts/admin-header.php';
        require __DIR__ . '/../../views/admin/categories/index.php';
        require __DIR__ . '/../../views/layouts/admin-footer.php';
    }

    public function store() {
        \Auth::requireAdmin();
        $db = \Database::getInstance();
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        $db->insert('categories', [
            'name'        => $name,
            'slug'        => \slug($name),
            'description' => $description,
        ]);

        \flash_set('success', 'Kategori berhasil ditambahkan.');
        header('Location: /geprek-geh/admin/categories');
        exit;
    }

    public function delete($id) {
        \Auth::requireAdmin();
        $db = \Database::getInstance();
        $db->delete('categories', 'id = ?', [$id]);
        \flash_set('success', 'Kategori berhasil dihapus.');
        header('Location: /geprek-geh/admin/categories');
        exit;
    }
}
