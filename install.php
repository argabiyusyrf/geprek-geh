<?php
/**
 * Geprek Geh — Installer & Seeder
 * Run: php install.php
 */

require_once __DIR__ . '/config/database.php';
$config = require __DIR__ . '/config/database.php';

echo "🍗 Geprek Geh — Installer\n";
echo str_repeat('─', 40) . "\n";

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};charset={$config['charset']}",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "✓ Koneksi database berhasil\n";

    // Run schema
    $sql = file_get_contents(__DIR__ . '/database/schema.sql');
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($statements as $stmt) {
        if (!empty($stmt)) {
            $pdo->exec($stmt);
        }
    }
    echo "✓ Schema database berhasil dijalankan\n";

    $pdo->exec("USE `{$config['dbname']}`");

    // Clean existing seed data (idempotent re-run)
    foreach (['cart', 'order_items', 'orders', 'products', 'categories'] as $t) {
        $pdo->exec("DELETE FROM `{$t}`");
    }

    // Seed admin + customer (password dari env bila tersedia, else random)
    $admin_pass = getenv('GEPREK_ADMIN_PASS') ?: bin2hex(random_bytes(4));
    $pdo->prepare("INSERT IGNORE INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')")
        ->execute(['Admin Geprek Geh', 'admin@geprekgeh.com', password_hash($admin_pass, PASSWORD_DEFAULT)]);

    $cust_pass = getenv('GEPREK_CUSTOMER_PASS') ?: bin2hex(random_bytes(4));
    $pdo->prepare("INSERT IGNORE INTO users (name, email, password, phone, role) VALUES (?, ?, ?, ?, 'customer')")
        ->execute(['Budi Santoso', 'budi@email.com', password_hash($cust_pass, PASSWORD_DEFAULT), '081234567890']);

    echo "✓ Akun admin: admin@geprekgeh.com / {$admin_pass}\n";
    echo "✓ Akun customer: budi@email.com / {$cust_pass}\n";

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
        // Geprek Original
        [$cat_ids['geprek-original'], 'Geprek Ayam Original', 'geprek-ayam-original', 'Ayam goreng crispy dengan sambal geprek original pedas nikmat', 18000, 50, 1, 1],
        [$cat_ids['geprek-original'], 'Geprek Paha Atas', 'geprek-paha-atas', 'Paha atas ayam goreng crispy + sambal geprek', 22000, 40, 1, 1],
        [$cat_ids['geprek-original'], 'Geprek Dada', 'geprek-dada', 'Dada ayam goreng crispy + sambal geprek', 20000, 35, 1, 0],
        [$cat_ids['geprek-original'], 'Geprek Sayap', 'geprek-sayap', 'Sayap ayam goreng crispy + sambal geprek', 16000, 30, 1, 0],

        // Geprek Level
        [$cat_ids['geprek-level'], 'Geprek Level 1 (Ringan)', 'geprek-level-1', 'Geprek dengan sambal level 1, cocok untuk pemula', 19000, 45, 1, 0],
        [$cat_ids['geprek-level'], 'Geprek Level 2 (Sedang)', 'geprek-level-2', 'Geprek dengan sambal level 2, pedas sedang', 19000, 45, 1, 1],
        [$cat_ids['geprek-level'], 'Geprek Level 3 (Pedas)', 'geprek-level-3', 'Geprek dengan sambal level 3, pedas menyengat!', 20000, 40, 1, 0],
        [$cat_ids['geprek-level'], 'Geprek Setan 🔥', 'geprek-setan', 'Level tertinggi! Hanya untuk yang berani!', 22000, 25, 1, 1],

        // Nasi Geprek
        [$cat_ids['nasi-geprek'], 'Nasi Geprek Ayam', 'nasi-geprek-ayam', 'Nasi putih + ayam geprek original + lalapan', 25000, 60, 1, 1],
        [$cat_ids['nasi-geprek'], 'Nasi Geprek Spesial', 'nasi-geprek-spesial', 'Nasi + ayam geprek + telur dadar + tempe + lalapan', 30000, 50, 1, 1],
        [$cat_ids['nasi-geprek'], 'Nasi Geprek Komplit', 'nasi-geprek-komplit', 'Nasi + ayam geprek + telur + tempe + tahu + lalapan', 35000, 40, 1, 0],

        // Minuman
        [$cat_ids['minuman'], 'Es Teh Manis', 'es-teh-manis', 'Teh manis dingin, segar!', 5000, 100, 1, 0],
        [$cat_ids['minuman'], 'Es Jeruk', 'es-jeruk', 'Jeruk peras segar dengan es batu', 8000, 80, 1, 0],
        [$cat_ids['minuman'], 'Es Kelapa Muda', 'es-kelapa-muda', 'Air kelapa muda segar', 10000, 50, 1, 0],
        [$cat_ids['minuman'], 'Aqua 600ml', 'aqua-600ml', 'Air mineral kemasan', 4000, 100, 1, 0],

        // Side Dish
        [$cat_ids['side-dish'], 'Nasi Putih', 'nasi-putih', 'Nasi putih hangat', 5000, 100, 1, 0],
        [$cat_ids['side-dish'], 'Telur Dadar', 'telur-dadar', 'Telur dadar goreng', 5000, 80, 1, 0],
        [$cat_ids['side-dish'], 'Tempe Goreng', 'tempe-goreng', 'Tempe goreng renyah', 4000, 70, 1, 0],
        [$cat_ids['side-dish'], 'Tahu Goreng', 'tahu-goreng', 'Tahu goreng renyah', 4000, 70, 1, 0],
        [$cat_ids['side-dish'], 'Lalapan', 'lalapan', 'Lalapan segar (timun, kemangi, kol)', 3000, 100, 1, 0],
    ];

    $product_ids = [];
    foreach ($products as [$cat_id, $name, $slug, $desc, $price, $stock, $active, $featured]) {
        $pdo->prepare("INSERT IGNORE INTO products (category_id, name, slug, description, price, stock, is_active, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute([$cat_id, $name, $slug, $desc, $price, $stock, $active, $featured]);
        $product_ids[$slug] = (int) $pdo->lastInsertId();
    }
    echo "✓ 20 produk berhasil ditambahkan\n";

    // Seed sample orders
    $insert_order = function ($args) use ($pdo) {
        $pdo->prepare("INSERT INTO orders (user_id, invoice_no, total, shipping_cost, tax, grand_total, status, payment_method, shipping_address, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
            ->execute($args);
        return (int) $pdo->lastInsertId();
    };
    $insert_item = function ($order_id, $product_slug, $qty, $price) use ($pdo, $product_ids) {
        $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)")
            ->execute([$order_id, $product_ids[$product_slug], $qty, $price]);
    };

    $order1 = $insert_order([2, 'GG-20260901-A1B2C3', 55000, 10000, 6050, 71050, 'processing', 'transfer', 'Jl. Merdeka No. 10, Jakarta Selatan', 'Level pedas sedang']);
    $insert_item($order1, 'nasi-geprek-spesial', 1, 30000);
    $insert_item($order1, 'geprek-setan', 1, 22000);
    $insert_item($order1, 'es-teh-manis', 1, 5000);

    $order2 = $insert_order([2, 'GG-20260901-D4E5F6', 44000, 10000, 4840, 58840, 'delivered', 'cod', 'Jl. Sudirman No. 5, Bandung', null]);
    $insert_item($order2, 'nasi-geprek-ayam', 1, 25000);
    $insert_item($order2, 'geprek-level-2', 1, 19000);

    echo "✓ 2 pesanan contoh berhasil ditambahkan\n";

    echo "\n" . str_repeat('─', 40) . "\n";
    echo "✅ Instalasi selesai!\n";
    echo "🌐 Buka: http://localhost/geprek-geh/\n";
    echo "🔑 Admin: admin@geprekgeh.com / admin123\n";
    echo "👤 Customer: budi@email.com / customer123\n";

} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
