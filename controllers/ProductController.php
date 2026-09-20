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

        $total = ProductRepo::publicCount($where, $params);
        $total_pages = max(1, ceil($total / $per_page));

        $order_map = [
            'populer'  => 'p.is_featured DESC, review_count DESC, p.created_at DESC, p.id DESC',
            'terbaru'  => 'p.created_at DESC, p.id DESC',
            'termurah' => 'p.price ASC, p.id ASC',
            'termahal' => 'p.price DESC, p.id DESC',
        ];
        $order_by = $order_map[$sort] ?? $order_map['populer'];

        $products = ProductRepo::publicList($where, $params, $order_by, $per_page, $offset);
        $categories = ProductRepo::categoriesWithCount();

        $app = require __DIR__ . '/../config/app.php';

        render('products/index', get_defined_vars());
    }

    public function show($slug) {
        $db = Database::getInstance();
        $product = ProductRepo::bySlug($slug);
        if (!$product) {
            http_response_code(404);
            require __DIR__ . '/../views/layouts/404.php';
            return;
        }
        $related = ProductRepo::related($product['category_id'], $product['id'], 4);

        // Reviews
        $reviews = $db->fetchAll(
            "SELECT pr.*, u.name AS user_name FROM product_reviews pr
             JOIN users u ON pr.user_id = u.id
             WHERE pr.product_id = ? AND pr.is_visible = 1 ORDER BY pr.created_at DESC",
            [$product['id']]
        );
        $review_stats = $db->fetchOne(
            "SELECT COUNT(*) AS review_count, COALESCE(AVG(rating), 0) AS avg_rating
             FROM product_reviews WHERE product_id = ? AND is_visible = 1",
            [$product['id']]
        );
        $my_review = Auth::check() ? $db->fetchOne(
            "SELECT * FROM product_reviews WHERE product_id = ? AND user_id = ?",
            [$product['id'], Auth::id()]
        ) : null;

        $has_delivered = false;
        if (Auth::check()) {
            $has_delivered = (int) $db->fetchColumn(
                "SELECT COUNT(*) FROM orders o
                 JOIN order_items oi ON oi.order_id = o.id
                 WHERE o.user_id = ? AND o.status = 'delivered' AND oi.product_id = ?",
                [Auth::id(), $product['id']]
            ) > 0;
        }

        $review_buyer_ids = [];
        if (!empty($reviews)) {
            $review_user_ids = array_unique(array_column($reviews, 'user_id'));
            $placeholders = implode(',', array_fill(0, count($review_user_ids), '?'));
            $review_buyer_ids = array_column(
                $db->fetchAll(
                    "SELECT DISTINCT o.user_id FROM orders o
                     JOIN order_items oi ON oi.order_id = o.id
                     WHERE o.user_id IN ({$placeholders}) AND o.status = 'delivered' AND oi.product_id = ?",
                    array_merge($review_user_ids, [$product['id']])
                ),
                'user_id'
            );
        }
        $rating_dist = array_fill(1, 5, 0);
        foreach ($db->fetchAll(
            "SELECT rating, COUNT(*) AS total FROM product_reviews WHERE product_id = ? AND is_visible = 1 GROUP BY rating",
            [$product['id']]
        ) as $row) {
            $rating_dist[(int)$row['rating']] = (int)$row['total'];
        }

        $app = require __DIR__ . '/../config/app.php';

        render('products/show', get_defined_vars());
    }
}
