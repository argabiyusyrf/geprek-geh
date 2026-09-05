<?php
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function rupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function slug($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9-]/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return rtrim($text, '-');
}

function redirect($url) {
    header("Location: {$url}");
    exit;
}

function flash($key = null) {
    if (!isset($_SESSION['flash'])) return null;
    $flash = $_SESSION['flash'];
    if ($key === null) {
        unset($_SESSION['flash']);
        return $flash;
    }
    return $flash[$key] ?? null;
}

function flash_set($type, $msg) {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
}

function verify_csrf() {
    $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function time_ago($datetime) {
    $now = new DateTime();
    $past = new DateTime($datetime);
    $diff = $now->diff($past);
    if ($diff->d > 0) return $diff->d . ' hari lalu';
    if ($diff->h > 0) return $diff->h . ' jam lalu';
    if ($diff->i > 0) return $diff->i . ' menit lalu';
    return 'Baru saja';
}

function generate_invoice() {
    return 'GG-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

function format_status($status) {
    $map = [
        'pending'    => ['Menunggu', 'badge-warning'],
        'processing' => ['Diproses', 'badge-info'],
        'shipped'    => ['Dikirim', 'badge-primary'],
        'delivered'  => ['Selesai', 'badge-success'],
        'cancelled'  => ['Dibatalkan', 'badge-danger'],
    ];
    return $map[$status] ?? [$status, 'badge-secondary'];
}

function format_payment_status($status) {
    $map = [
        'unpaid'   => ['Belum Dibayar', 'badge-warning'],
        'paid'     => ['Lunas', 'badge-success'],
        'refunded' => ['Refund', 'badge-secondary'],
    ];
    return $map[$status] ?? [$status, 'badge-secondary'];
}

function order_log($db, $order_id, $actor, $message) {
    $db->insert('order_logs', ['order_id' => $order_id, 'actor' => $actor, 'message' => $message]);
}

function order_restore_stock($db, $order_id) {
    $items = $db->fetchAll("SELECT product_id, quantity FROM order_items WHERE order_id = ?", [$order_id]);
    foreach ($items as $item) {
        $db->query("UPDATE products SET stock = stock + ? WHERE id = ?", [$item['quantity'], $item['product_id']]);
    }
}

function wa_link($number) {
    $n = preg_replace('/\D/', '', $number ?? '');
    if (str_starts_with($n, '0')) $n = '62' . substr($n, 1);
    return 'https://wa.me/' . $n;
}
