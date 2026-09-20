<?php
/**
 * ProductRepo — satu-satunya tempat query produk yang dipakai bersama.
 * Semua method mengembalikan data mentah dari Database singleton.
 */
class ProductRepo {

    private static function db() {
        return Database::getInstance();
    }

    /** Fragmen LEFT JOIN agregasi rating/ulasan (hanya ulasan terlihat). */
    private static function reviewJoin(): string {
        return "LEFT JOIN (
                SELECT product_id, COUNT(*) AS review_count, AVG(rating) AS avg_rating
                FROM product_reviews WHERE is_visible = 1 GROUP BY product_id
              ) r ON r.product_id = p.id";
    }

    /** Daftar produk publik dengan kategori + rating (slug kategori ikut diambil). */
    public static function publicList(string $where, array $params, string $orderBy, int $limit, int $offset): array {
        $limit = (int) $limit;
        $offset = (int) $offset;
        return self::db()->fetchAll(
            "SELECT p.*, c.name AS category_name, c.slug AS category_slug,
              COALESCE(r.review_count, 0) AS review_count,
              COALESCE(r.avg_rating, 0) AS avg_rating
              FROM products p JOIN categories c ON p.category_id = c.id
              " . self::reviewJoin() . "
              WHERE {$where} ORDER BY {$orderBy} LIMIT {$limit} OFFSET {$offset}",
            $params
        );
    }

    /** Hitung jumlah produk publik yang cocok (butuh join kategori utk filter slug). */
    public static function publicCount(string $where, array $params): int {
        return (int) self::db()->fetchColumn(
            "SELECT COUNT(*) FROM products p JOIN categories c ON p.category_id = c.id WHERE {$where}",
            $params
        );
    }

    /** Produk tunggal publik berdasar slug (detail halaman produk). */
    public static function bySlug(string $slug): ?array {
        $row = self::db()->fetchOne(
            "SELECT p.*, c.name AS category_name, c.slug AS category_slug
             FROM products p JOIN categories c ON p.category_id = c.id
             WHERE p.slug = ? AND p.is_active = 1",
            [$slug]
        );
        return $row ?: null;
    }

    /** Produk terkait satu kategori (selain produk ini), acak. */
    public static function related(int $categoryId, int $excludeId, int $limit = 4): array {
        return self::db()->fetchAll(
            "SELECT p.*, c.name AS category_name, c.slug AS category_slug,
              COALESCE(r.review_count, 0) AS review_count,
              COALESCE(r.avg_rating, 0) AS avg_rating
             FROM products p JOIN categories c ON p.category_id = c.id
             " . self::reviewJoin() . "
             WHERE p.is_active = 1 AND p.category_id = ? AND p.id != ? ORDER BY RAND() LIMIT " . (int) $limit,
            [$categoryId, $excludeId]
        );
    }

    /** Daftar kategori + jumlah produk aktif (dipakai home & halaman produk). */
    public static function categoriesWithCount(): array {
        return self::db()->fetchAll(
            "SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id AND is_active = 1) AS product_count FROM categories c ORDER BY c.sort_order, c.name"
        );
    }

    /** Daftar produk admin/stok: tanpa agregasi rating, memakai kolom dari $where. */
    public static function adminList(string $where, array $params, string $orderBy, int $limit, int $offset): array {
        $limit = (int) $limit;
        $offset = (int) $offset;
        return self::db()->fetchAll(
            "SELECT p.*, c.name AS category_name
               FROM products p JOIN categories c ON p.category_id = c.id
              WHERE {$where} ORDER BY {$orderBy} LIMIT {$limit} OFFSET {$offset}",
            $params
        );
    }

    /** Produk tunggal berdasar id (detail stok/admin). */
    public static function byId(int $id): ?array {
        $row = self::db()->fetchOne(
            "SELECT p.*, c.name AS category_name FROM products p
             JOIN categories c ON p.category_id = c.id WHERE p.id = ?",
            [$id]
        );
        return $row ?: null;
    }

    /** KPI satu baris utk halaman manajemen stok. */
    public static function stockKpi(int $threshold): ?array {
        return self::db()->fetchOne(
            "SELECT
                COUNT(*) AS total,
                COALESCE(SUM(CASE WHEN p.stock <= ? THEN 1 ELSE 0 END), 0) AS low,
                COALESCE(SUM(CASE WHEN p.stock = 0 THEN 1 ELSE 0 END), 0) AS `out`,
                COALESCE(SUM(p.stock), 0) AS total_stock
             FROM products p JOIN categories c ON p.category_id = c.id
             WHERE p.is_active = 1",
            [$threshold]
        );
    }

    /** Produk stok menipis (peringatan dashboard & panel admin). */
    public static function lowStock(int $threshold, int $limit, bool $withImage = false): array {
        $cols = $withImage
            ? 'p.id, p.name, p.image, p.stock, c.name AS category_name'
            : 'p.id, p.name, p.stock, c.name AS category_name';
        return self::db()->fetchAll(
            "SELECT {$cols}
               FROM products p JOIN categories c ON p.category_id = c.id
              WHERE p.is_active = 1 AND p.stock <= ?
              ORDER BY p.stock ASC, p.name ASC LIMIT " . (int) $limit,
            [(int) $threshold]
        );
    }
}