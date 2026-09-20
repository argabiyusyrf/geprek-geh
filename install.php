<?php
/**
 * Geprek Geh — Installer & Seeder
 * Run: php install.php              (seed penuh: admin + customer + kategori + produk + order)
 *      php install.php --empty      (DB benar-benar kosong, HANYA akun admin)
 */

require_once __DIR__ . '/config/database.php';
$config = require __DIR__ . '/config/database.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Installer hanya bisa dijalankan dari CLI: php install.php');
}

$args = $_SERVER['argv'] ?? [];
$emptySeed = in_array('--empty', $args, true);

$admin_name  = getenv('GEPREK_ADMIN_NAME')  ?: 'Admin Geprek Geh';
$admin_email = getenv('GEPREK_ADMIN_EMAIL') ?: 'admin@geprekgeh.com';
$admin_pass  = getenv('GEPREK_ADMIN_PASS')  ?: 'AdminGeprek123';

echo "🍗 Geprek Geh — Installer" . ($emptySeed ? " [mode kosong — hanya admin]" : "") . "\n";
echo str_repeat('─', 40) . "\n";

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "✓ Koneksi database berhasil ({$config['dbname']})\n";

    // Run schema — skip CREATE DATABASE/USE agar tidak bergantung nama DB lokal
    // (DB remote InfinityFree punya nama sendiri, mis. if0_xxx_geprekgeh).
    $sql = file_get_contents(__DIR__ . '/database/schema.sql');
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($statements as $stmt) {
        if (!empty($stmt)) {
            $head = strtoupper(substr(trim($stmt), 0, 30));
            if (str_starts_with($head, 'CREATE DATABASE') || str_starts_with($head, 'USE ')) {
                continue;
            }
            $pdo->exec($stmt);
        }
    }
    echo "✓ Schema database berhasil dijalankan\n";

    // Bersihkan semua data (idempotent re-run) supaya DB benar-benar kosong.
    foreach (['cart', 'order_items', 'orders', 'products', 'categories', 'addresses',
              'notifications', 'order_logs', 'product_reviews', 'promo_codes', 'wishlists',
              'stock_movements', 'password_resets', 'remember_tokens', 'sessions'] as $t) {
        $pdo->exec("DELETE FROM `{$t}`");
    }
    $pdo->exec("DELETE FROM `users` WHERE role <> 'admin'");

    // Seed akun admin (satu-satunya data yang dipertahankan).
    $pdo->prepare("INSERT IGNORE INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')")
        ->execute([$admin_name, $admin_email, password_hash($admin_pass, PASSWORD_DEFAULT)]);

    echo "✓ Akun admin: {$admin_email} / {$admin_pass}\n";

    if ($emptySeed) {
        echo "\n" . str_repeat('─', 40) . "\n";
        echo "✅ Instalasi selesai (mode kosong)!\n";
        echo "🌐 Buka: " . ($_SERVER['HTTP_HOST'] ?? '/') . "\n";
        echo "🔑 Admin: {$admin_email} / {$admin_pass}\n";
        exit(0);
    }

    // Seed customer + kategori + produk + order contoh (mode penuh).
    $cust_pass = 'Argaabiyyu123';
    $pdo->prepare("INSERT IGNORE INTO users (name, email, password, phone, role) VALUES (?, ?, ?, ?, 'customer')")
        ->execute(['Arga Abiyu', 'argaabiyyu@email.com', password_hash($cust_pass, PASSWORD_DEFAULT), '081234567890']);

    echo "✓ Akun customer: argaabiyyu@email.com / {$cust_pass}\n";

    // Seed categories
    $categories = [
        ['Geprek Original', 'geprek-original', 'Ayam geprek original dengan sambal khas', 1],
        ['Geprek Level', 'geprek-level', 'Geprek dengan level kepedasan', 2],
        ['Nasi Geprek', 'nasi-geprek', 'Geprek lengkap dengan nasi putih', 3],
        ['Minuman', 'minuman', 'Minuman segar pendamping geprek', 4],
        ['Side Dish', 'side-dish', 'Lauk dan pelengkap', 5],
    ];

    $cat_ids = [];
    foreach ($categories as [$name, $slug, $desc, $sort]) {
        $pdo->prepare("INSERT IGNORE INTO categories (name, slug, description, sort_order) VALUES (?, ?, ?, ?)")
            ->execute([$name, $slug, $desc, $sort]);
        $cat_ids[$slug] = (int) $pdo->lastInsertId();
    }
    echo "✓ 5 kategori berhasil ditambahkan\n";

    // Seed products
    $products = [
        // Makanan
        [$cat_ids['nasi-geprek'], 'Nasi Geprek Ikan Asin', 'nasi-geprek-ikan-asin', 'Nasi putih + ayam geprek + ikan asin goreng + sambal + lalapan', 28000, 40, 1, 1],
        [$cat_ids['geprek-original'], 'Geprek Tulang Lunak', 'geprek-tulang-lunak', 'Tulang lunak ayam goreng crispy digeprek dengan sambal spesial', 24000, 30, 1, 1],

        // Minuman
        [$cat_ids['minuman'], 'Es Jeruk Nipis', 'es-jeruk-nipis', 'Jeruk nipis peras segar dengan es batu, menyegarkan', 6000, 80, 1, 0],
        [$cat_ids['minuman'], 'Es Cimol Susu', 'es-cimol-susu', 'Cimol kenyal dengan susu coklat dingin dan es batu', 12000, 60, 1, 1],
    ];

    $product_ids = [];
    foreach ($products as [$cat_id, $name, $slug, $desc, $price, $stock, $active, $featured]) {
        $pdo->prepare("INSERT IGNORE INTO products (category_id, name, slug, description, price, stock, is_active, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute([$cat_id, $name, $slug, $desc, $price, $stock, $active, $featured]);
        $product_ids[$slug] = (int) $pdo->lastInsertId();
    }
    echo "✓ 4 produk berhasil ditambahkan\n";

    // Seed sample orders (use actual customer user_id)
    $cust_row = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $cust_row->execute(['argaabiyyu@email.com']);
    $cust_user_id = (int) $cust_row->fetchColumn();
    if ($cust_user_id < 1) { $cust_user_id = 2; }

    $insert_order = function ($args) use ($pdo) {
        $pdo->prepare("INSERT INTO orders (user_id, invoice_no, total, shipping_cost, tax, status, payment_method, shipping_address, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute($args);
        return (int) $pdo->lastInsertId();
    };
    $insert_item = function ($order_id, $product_slug, $qty, $price) use ($pdo, $product_ids) {
        $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)")
            ->execute([$order_id, $product_ids[$product_slug], $qty, $price]);
    };

    $order1 = $insert_order([$cust_user_id, 'GG-20260908-X1Y2Z3', 78000, 10000, 8580, 'processing', 'transfer', 'Jl. Merdeka No. 10, Jakarta Selatan', null]);
    $insert_item($order1, 'nasi-geprek-ikan-asin', 1, 28000);
    $insert_item($order1, 'geprek-tulang-lunak', 1, 24000);
    $insert_item($order1, 'es-jeruk-nipis', 1, 6000);
    $insert_item($order1, 'es-cimol-susu', 1, 12000);

    echo "✓ 1 pesanan contoh berhasil ditambahkan (4 item)\n";

    echo "\n" . str_repeat('─', 40) . "\n";
    echo "✅ Instalasi selesai!\n";
    echo "🌐 Buka: " . ($_SERVER['HTTP_HOST'] ?? '/') . "\n";
    echo "🔑 Admin: {$admin_email} / {$admin_pass}\n";
    echo "👤 Customer: argaabiyyu@email.com / {$cust_pass}\n";

} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
