<?php
/**
 * Add missing indexes for 3NF schema performance.
 * Run once: php database/migrations/add_indexes.php
 */
require_once __DIR__ . '/../../core/Database.php';

$db = Database::getInstance();

$indexes = [
    ['table' => 'products',        'name' => 'idx_products_active',      'cols' => 'is_active'],
    ['table' => 'products',        'name' => 'idx_products_category',    'cols' => 'category_id'],
    ['table' => 'products',        'name' => 'idx_products_featured',    'cols' => 'is_featured'],
    ['table' => 'orders',          'name' => 'idx_orders_user',          'cols' => 'user_id'],
    ['table' => 'orders',          'name' => 'idx_orders_status',        'cols' => 'status'],
    ['table' => 'orders',          'name' => 'idx_orders_payment',       'cols' => 'payment_status'],
    ['table' => 'order_items',     'name' => 'idx_order_items_order',    'cols' => 'order_id'],
    ['table' => 'cart',            'name' => 'idx_cart_user',            'cols' => 'user_id'],
    ['table' => 'cart',            'name' => 'idx_cart_session',         'cols' => 'session_id'],
    ['table' => 'product_reviews', 'name' => 'idx_reviews_product',      'cols' => 'product_id'],
    ['table' => 'addresses',       'name' => 'idx_addresses_user',       'cols' => 'user_id'],
    ['table' => 'sessions',        'name' => 'idx_sessions_user',        'cols' => 'user_id'],
];

$added = 0;
foreach ($indexes as $idx) {
    $check = $db->fetchColumn(
        "SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?",
        [$idx['table'], $idx['name']]
    );
    if (!$check) {
        $db->query("CREATE INDEX {$idx['name']} ON {$idx['table']} ({$idx['cols']})");
        echo "✓ Added index {$idx['name']} on {$idx['table']}({$idx['cols']})\n";
        $added++;
    } else {
        echo "– Index {$idx['name']} already exists\n";
    }
}

echo "\nDone. {$added} indexes added.\n";
