<?php
class HomeController {
    public function index() {
        $db = Database::getInstance();
        $featured = $db->fetchAll("SELECT p.*, c.name AS category_name,
              COALESCE(r.review_count, 0) AS review_count,
              COALESCE(r.avg_rating, 0) AS avg_rating
              FROM products p JOIN categories c ON p.category_id = c.id
              LEFT JOIN (SELECT product_id, COUNT(*) AS review_count, AVG(rating) AS avg_rating FROM product_reviews GROUP BY product_id) r ON r.product_id = p.id
              WHERE p.is_active = 1 AND p.is_featured = 1 ORDER BY p.created_at DESC LIMIT 8");
        $latest = $db->fetchAll("SELECT p.*, c.name AS category_name,
              COALESCE(r.review_count, 0) AS review_count,
              COALESCE(r.avg_rating, 0) AS avg_rating
              FROM products p JOIN categories c ON p.category_id = c.id
              LEFT JOIN (SELECT product_id, COUNT(*) AS review_count, AVG(rating) AS avg_rating FROM product_reviews GROUP BY product_id) r ON r.product_id = p.id
              WHERE p.is_active = 1 ORDER BY p.created_at DESC LIMIT 8");
        $categories = $db->fetchAll("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id AND is_active = 1) AS product_count FROM categories c ORDER BY c.sort_order, c.name");

        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/home/index.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }
}
