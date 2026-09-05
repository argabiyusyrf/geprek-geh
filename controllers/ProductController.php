<?php
class ProductController {
    public function index() {
        $db = Database::getInstance();
        $cat = $_GET['category'] ?? null;
        $search = $_GET['q'] ?? null;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $per_page = 12;
        $offset = ($page - 1) * $per_page;

        $where = "p.is_active = 1";
        $params = [];

        if ($cat) {
            $where .= " AND c.slug = ?";
            $params[] = $cat;
        }
        if ($search) {
            $where .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $total = $db->fetchColumn(
            "SELECT COUNT(*) FROM products p JOIN categories c ON p.category_id = c.id WHERE {$where}",
            $params
        );
        $total_pages = max(1, ceil($total / $per_page));

        $products = $db->fetchAll(
            "SELECT p.*, c.name AS category_name, c.slug AS category_slug
             FROM products p JOIN categories c ON p.category_id = c.id
             WHERE {$where} ORDER BY p.is_featured DESC, p.created_at DESC LIMIT {$per_page} OFFSET {$offset}",
            $params
        );
        $categories = $db->fetchAll("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id AND is_active = 1) AS product_count FROM categories c ORDER BY c.sort_order, c.name");

        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/products/index.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }

    public function show($slug) {
        $db = Database::getInstance();
        $product = $db->fetchOne(
            "SELECT p.*, c.name AS category_name, c.slug AS category_slug
             FROM products p JOIN categories c ON p.category_id = c.id
             WHERE p.slug = ? AND p.is_active = 1",
            [$slug]
        );
        if (!$product) {
            http_response_code(404);
            require __DIR__ . '/../views/layouts/404.php';
            return;
        }
        $related = $db->fetchAll(
            "SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id
             WHERE p.is_active = 1 AND p.category_id = ? AND p.id != ? ORDER BY RAND() LIMIT 4",
            [$product['category_id'], $product['id']]
        );

        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/products/show.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }
}
