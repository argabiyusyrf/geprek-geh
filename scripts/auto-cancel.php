<?php
/**
 * Auto-cancel pesanan yang belum dibayar (status=pending & payment_status=unpaid)
 * lewat batas waktu yang ditentukan di pengaturan toko (auto_cancel_hours).
 *
 * Jalankan via cron, setiap 30 menit (lihat contoh di AGENTS / crontab):
 *   cd /var/www/html/geprek-geh && php scripts/auto-cancel.php >> logs/auto-cancel.log 2>&1
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Hanya bisa dijalankan dari CLI');
}

$config = require __DIR__ . '/../config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (Throwable $e) {
    fwrite(STDERR, 'DB error: ' . $e->getMessage() . "\n");
    exit(1);
}

// Read settings
$getSetting = function (string $key, string $default) use ($pdo) {
    $st = $pdo->prepare("SELECT svalue FROM toko_settings WHERE skey = ? LIMIT 1");
    $st->execute([$key]);
    $v = $st->fetchColumn();
    return $v === false || $v === null || $v === '' ? $default : (string) $v;
};

$enabled = (int) $getSetting('auto_cancel_enabled', '1');
if ($enabled !== 1) {
    echo "[auto-cancel] Nonaktif (auto_cancel_enabled=0)\n";
    exit(0);
}

$hours = max(1, (int) $getSetting('auto_cancel_hours', '24'));
// Hitung cutoff dengan jam MySQL (NOW()) agar sejalan dengan kolom created_at.
$cutoff = $pdo->prepare("SELECT DATE_SUB(NOW(), INTERVAL ? HOUR)");
$cutoff->execute([$hours]);
$cutoff = (string) $cutoff->fetchColumn();

$st = $pdo->prepare(
    "SELECT o.id, o.invoice_no, o.user_id, u.name AS customer_name
       FROM orders o JOIN users u ON o.user_id = u.id
      WHERE o.status = 'pending' AND o.payment_status = 'unpaid'
        AND o.created_at <= ?
      ORDER BY o.created_at ASC"
);
$st->execute([$cutoff]);
$orders = $st->fetchAll(PDO::FETCH_ASSOC);

if (!$orders) {
    echo "[auto-cancel] " . date('Y-m-d H:i:s') . " — Tidak ada pesanan yang kadaluarsa (batas {$hours} jam).\n";
    exit(0);
}

$restore = $pdo->prepare(
    "SELECT order_id, product_id, quantity FROM order_items WHERE order_id = ?
       ORDER BY id ASC"
);
$upStock = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
$insMove = $pdo->prepare(
    "INSERT INTO stock_movements (product_id, qty_change, note, user_id)
     VALUES (?, ?, 'Pembatalan otomatis', NULL)"
);
$upOrder = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
$logStmt = $pdo->prepare(
    "INSERT INTO order_logs (order_id, actor, message) VALUES (?, 'admin', ?)"
);
$notifStmt = $pdo->prepare(
    "INSERT INTO notifications (user_id, type, title, message, link)
     VALUES (?, 'info', 'Pesanan dibatalkan otomatis', ?, ?)"
);

$pdo->beginTransaction();
$count = 0;
try {
    foreach ($orders as $o) {
        $restore->execute([$o['id']]);
        foreach ($restore->fetchAll(PDO::FETCH_ASSOC) as $item) {
            $upStock->execute([(int) $item['quantity'], (int) $item['product_id']]);
            $insMove->execute([(int) $item['product_id'], (int) $item['quantity']]);
        }
        $upOrder->execute([$o['id']]);
        $logStmt->execute([
            $o['id'],
            "Pesanan dibatalkan otomatis karena tidak dibayar dalam {$hours} jam.",
        ]);
        $notifStmt->execute([
            $o['user_id'],
            "Pesanan kamu {$o['invoice_no']} dibatalkan karena pembayaran belum selesai dalam {$hours} jam. Stok sudah dikembalikan.",
            '/geprek-geh/orders/' . $o['id'],
        ]);
        $count++;
        echo "[auto-cancel] #{$o['invoice_no']} (id={$o['id']}, {$o['customer_name']}) dibatalkan otomatis.\n";
    }
    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, '[auto-cancel] ERROR: ' . $e->getMessage() . "\n");
    exit(1);
}

echo "[auto-cancel] Selesai: {$count} pesanan dibatalkan otomatis (batas {$hours} jam, cutoff {$cutoff}).\n";