<?php
class SearchController {
    /** Suggestions endpoint used by the header overlay. JSON output. */
    public function suggest() {
        header('Content-Type: application/json; charset=utf-8');
        $q = trim($_GET['q'] ?? '');
        if (mb_strlen($q) < 2) {
            echo json_encode(['ok' => true, 'items' => [], 'total' => 0]);
            return;
        }
        $db = Database::getInstance();
        $like = '%' . $q . '%';
        $rows = $db->fetchAll(
            "SELECT p.id, p.name, p.slug, p.image, p.price, c.name AS category_name, c.slug AS category_slug
             FROM products p
             JOIN categories c ON p.category_id = c.id
             WHERE p.is_active = 1 AND (p.name LIKE ? OR c.name LIKE ?)
             ORDER BY p.is_featured DESC, p.name
             LIMIT 6",
            [$like, $like]
        );
        $totalRow = $db->fetchOne(
            "SELECT COUNT(*) AS c FROM products p
             JOIN categories c ON p.category_id = c.id
             WHERE p.is_active = 1 AND (p.name LIKE ? OR c.name LIKE ?)",
            [$like, $like]
        );
        echo json_encode([
            'ok'    => true,
            'items' => array_map(function ($r) {
                return [
                    'id'    => (int) $r['id'],
                    'name'  => $r['name'],
                    'slug'  => $r['slug'],
                    'price' => (int) $r['price'],
                    'category' => $r['category_name'],
                    'category_slug' => $r['category_slug'],
                    'image' => $r['image'] ? '/geprek-geh/assets/uploads/products/' . $r['image'] : null,
                    'href'  => '/geprek-geh/products/' . $r['slug'],
                ];
            }, $rows),
            'total' => (int) $totalRow['c'],
            'view_all_href' => '/geprek-geh/products?q=' . urlencode($q),
        ]);
    }

    /** Dedicated search results page (uses existing /products with ?q=). */
    public function page() {
        $q = trim($_GET['q'] ?? '');
        header('Location: /geprek-geh/products?q=' . urlencode($q));
        exit;
    }
}