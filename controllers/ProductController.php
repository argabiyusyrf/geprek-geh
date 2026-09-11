<?php
class ProductController {
    public function index() {
        $db = Database::getInstance();
        $cat = $_GET['category'] ?? null;
        $search = $_GET['q'] ?? null;
        $sort = $_GET['sort'] ?? 'populer';
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

        $order_map = [
            'populer'  => 'p.is_featured DESC, review_count DESC, p.created_at DESC, p.id DESC',
            'terbaru'  => 'p.created_at DESC, p.id DESC',
            'termurah' => 'p.price ASC, p.id ASC',
            'termahal' => 'p.price DESC, p.id DESC',
        ];
        $order_by = $order_map[$sort] ?? $order_map['populer'];

        $products = $db->fetchAll(
            "SELECT p.*, c.name AS category_name, c.slug AS category_slug,
              COALESCE(r.review_count, 0) AS review_count,
              COALESCE(r.avg_rating, 0) AS avg_rating
              FROM products p JOIN categories c ON p.category_id = c.id
              LEFT JOIN (
                SELECT product_id, COUNT(*) AS review_count, AVG(rating) AS avg_rating
                FROM product_reviews GROUP BY product_id
              ) r ON r.product_id = p.id
              WHERE {$where} ORDER BY {$order_by} LIMIT {$per_page} OFFSET {$offset}",
            $params
        );
        $categories = $db->fetchAll("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id AND is_active = 1) AS product_count FROM categories c ORDER BY c.sort_order, c.name");

        $app = require __DIR__ . '/../config/app.php';

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
            "SELECT p.*, c.name AS category_name, c.slug AS category_slug,
              COALESCE(r.review_count, 0) AS review_count,
              COALESCE(r.avg_rating, 0) AS avg_rating
             FROM products p JOIN categories c ON p.category_id = c.id
             LEFT JOIN (
                SELECT product_id, COUNT(*) AS review_count, AVG(rating) AS avg_rating
                FROM product_reviews GROUP BY product_id
             ) r ON r.product_id = p.id
             WHERE p.is_active = 1 AND p.category_id = ? AND p.id != ? ORDER BY RAND() LIMIT 4",
            [$product['category_id'], $product['id']]
        );

        // Reviews
        $reviews = $db->fetchAll(
            "SELECT pr.*, u.name AS user_name FROM product_reviews pr
             JOIN users u ON pr.user_id = u.id
             WHERE pr.product_id = ? ORDER BY pr.created_at DESC",
            [$product['id']]
        );
        $review_stats = $db->fetchOne(
            "SELECT COUNT(*) AS review_count, COALESCE(AVG(rating), 0) AS avg_rating
             FROM product_reviews WHERE product_id = ?",
            [$product['id']]
        );
        $my_review = Auth::check() ? $db->fetchOne(
            "SELECT * FROM product_reviews WHERE product_id = ? AND user_id = ?",
            [$product['id'], Auth::id()]
        ) : null;
        $rating_dist = array_fill(1, 5, 0);
        foreach ($db->fetchAll(
            "SELECT rating, COUNT(*) AS total FROM product_reviews WHERE product_id = ? GROUP BY rating",
            [$product['id']]
        ) as $row) {
            $rating_dist[(int)$row['rating']] = (int)$row['total'];
        }

        $app = require __DIR__ . '/../config/app.php';

        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/products/show.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }
}
