<?php
class SeoController {
    public function sitemap() {
        $db = Database::getInstance();
        $base = 'http' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 's' : '') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/geprek-geh';

        $urls = [];
        // Static routes
        foreach (['/' => '1.0', '/products' => '0.9'] as $path => $pri) {
            $urls[] = ['loc' => $base . $path, 'priority' => $pri, 'changefreq' => 'daily'];
        }

        // Categories (only include column if it exists)
        try {
            $cols = $db->fetchAll("SHOW COLUMNS FROM categories LIKE 'updated_at'");
            $catHasUpdated = !empty($cols);
        } catch (Exception $e) { $catHasUpdated = false; }

        foreach ($db->fetchAll("SELECT slug" . ($catHasUpdated ? ", updated_at" : "") . " FROM categories ORDER BY id") as $c) {
            $urls[] = [
                'loc'        => $base . '/products?category=' . urlencode($c['slug']),
                'priority'   => '0.7',
                'changefreq' => 'weekly',
                'lastmod'    => $catHasUpdated ? ($c['updated_at'] ?? null) : null,
            ];
        }

        // Products
        foreach ($db->fetchAll("SELECT slug, updated_at FROM products WHERE is_active = 1") as $p) {
            $urls[] = [
                'loc'        => $base . '/products/' . urlencode($p['slug']),
                'priority'   => '0.8',
                'changefreq' => 'weekly',
                'lastmod'    => $p['updated_at'] ?? null,
            ];
        }

        header('Content-Type: application/xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            echo "  <url>\n";
            echo "    <loc>" . htmlspecialchars($u['loc']) . "</loc>\n";
            if (!empty($u['lastmod'])) echo "    <lastmod>" . date('Y-m-d', strtotime($u['lastmod'])) . "</lastmod>\n";
            echo "    <changefreq>" . $u['changefreq'] . "</changefreq>\n";
            echo "    <priority>" . $u['priority'] . "</priority>\n";
            echo "  </url>\n";
        }
        echo "</urlset>\n";
        exit;
    }

    public function robots() {
        $base = 'http' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 's' : '') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/geprek-geh';
        header('Content-Type: text/plain; charset=utf-8');
        echo "User-agent: *\n";
        echo "Disallow: /admin\n";
        echo "Disallow: /cart\n";
        echo "Disallow: /checkout\n";
        echo "Disallow: /account\n";
        echo "Disallow: /orders\n";
        echo "Disallow: /auth\n";
        echo "Allow: /\n";
        echo "Allow: /products\n";
        echo "Sitemap: " . $base . "/sitemap.xml\n";
        exit;
    }

    /** Inject JSON-LD into product detail view. */
    public static function productJsonLd(array $product): string {
        $base = 'http' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 's' : '') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/geprek-geh';
        $ld = [
            '@context'    => 'https://schema.org',
            '@type'       => 'Product',
            'name'        => $product['name'],
            'description' => mb_substr(strip_tags($product['description'] ?? ''), 0, 250),
            'url'         => $base . '/products/' . $product['slug'],
            'image'       => !empty($product['image']) ? $base . '/assets/uploads/products/' . $product['image'] : null,
            'sku'         => 'GG-' . $product['id'],
            'brand'       => ['@type' => 'Brand', 'name' => 'Geprek Geh'],
            'offers'      => [
                '@type'         => 'Offer',
                'price'         => number_format($product['price'], 0, '.', ''),
                'priceCurrency' => 'IDR',
                'availability'  => $product['stock'] > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                'url'           => $base . '/products/' . $product['slug'],
            ],
        ];
        return '<script type="application/ld+json">' . json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    }

    public static function organizationJsonLd(): string {
        $base = 'http' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 's' : '') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/geprek-geh';
        $ld = [
            '@context'    => 'https://schema.org',
            '@type'       => 'Restaurant',
            'name'        => 'Geprek Geh',
            'image'       => $base . '/public/favicon.svg',
            'url'         => $base . '/',
            'servesCuisine' => 'Indonesian',
            'priceRange'  => 'Rp',
            'telephone'   => '+62 812-3456-7890',
        ];
        return '<script type="application/ld+json">' . json_encode($ld, JSON_UNESCAPED_SLASHES) . '</script>';
    }
}