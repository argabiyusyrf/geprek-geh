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

        $cstats = [
            'total'    => count($categories),
            'products' => array_sum(array_column($categories, 'product_count')),
            'avg'      => $categories ? round(array_sum(array_column($categories, 'product_count')) / count($categories), 1) : 0,
            'empty'    => count(array_filter($categories, function ($c) { return (int) $c['product_count'] === 0; })),
        ];

        $formErrors = \form_errors();
        $formOld    = \form_old();

        require __DIR__ . '/../../views/layouts/admin-header.php';
        require __DIR__ . '/../../views/admin/categories/index.php';
        require __DIR__ . '/../../views/layouts/admin-footer.php';
    }

    private function validate(array $post, ?int $ignoreId = null): array {
        $errors = [];
        $db = \Database::getInstance();
        $name = trim($post['name'] ?? '');
        if ($name === '') {
            $errors['name'] = 'Nama kategori wajib diisi.';
        } elseif ($db->fetchOne("SELECT id FROM categories WHERE slug = ? AND id != ?", [\slug($name), (int) ($ignoreId ?? 0)])) {
            $errors['name'] = 'Kategori dengan nama serupa sudah ada.';
        }
        return $errors;
    }

    public function store() {
        \Auth::requireAdmin();
        if (!\verify_csrf()) { \flash_set('error', 'Token tidak valid.'); header('Location: /geprek-geh/admin/categories'); exit; }
        $db = \Database::getInstance();
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $sortOrder = max(0, (int) ($_POST['sort_order'] ?? 0));

        $errors = $this->validate($_POST);
        if ($errors) {
            \form_stash($errors, ['name' => $name, 'description' => $description, 'sort_order' => $sortOrder]);
            \flash_set('error', 'Mohon periksa kembali isian form kategori.');
            header('Location: /geprek-geh/admin/categories?create=1&error=1'); exit;
        }

        $db->insert('categories', [
            'name'        => $name,
            'slug'        => \slug($name),
            'description' => $description,
            'sort_order'  => $sortOrder,
        ]);

        \flash_set('success', 'Kategori "' . $name . '" berhasil ditambahkan.');
        header('Location: /geprek-geh/admin/categories');
        exit;
    }

    public function update($id) {
        \Auth::requireAdmin();
        $id = (int) $id;
        if (!\verify_csrf()) { \flash_set('error', 'Token tidak valid.'); header('Location: /geprek-geh/admin/categories'); exit; }
        $db = \Database::getInstance();
        if (!$db->fetchOne("SELECT id FROM categories WHERE id = ?", [$id])) {
            \flash_set('error', 'Kategori tidak ditemukan.');
            header('Location: /geprek-geh/admin/categories'); exit;
        }
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $sortOrder = max(0, (int) ($_POST['sort_order'] ?? 0));

        $errors = $this->validate($_POST, $id);
        if ($errors) {
            \form_stash($errors, ['name' => $name, 'description' => $description, 'sort_order' => $sortOrder]);
            \flash_set('error', 'Mohon periksa kembali isian form kategori.');
            header('Location: /geprek-geh/admin/categories?edit=' . $id . '&error=1'); exit;
        }

        $db->update('categories', [
            'name'        => $name,
            'slug'        => \slug($name),
            'description' => $description,
            'sort_order'  => $sortOrder,
        ], 'id = ?', [$id]);

        \flash_set('success', 'Kategori berhasil diupdate.');
        header('Location: /geprek-geh/admin/categories');
        exit;
    }

    public function delete($id) {
        \Auth::requireAdmin();
        if (!\verify_csrf()) { \flash_set('error', 'Token tidak valid.'); header('Location: /geprek-geh/admin/categories'); exit; }
        $db = \Database::getInstance();
        $cat = $db->fetchOne("SELECT id FROM categories WHERE id = ?", [$id]);
        if (!$cat) {
            \flash_set('error', 'Kategori tidak ditemukan.');
            header('Location: /geprek-geh/admin/categories'); exit;
        }
        $db->delete('categories', 'id = ?', [$id]);
        \flash_set('success', 'Kategori berhasil dihapus.');
        header('Location: /geprek-geh/admin/categories');
        exit;
    }
}