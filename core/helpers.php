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

/**
 * Placeholder "food art" premium — digunakan saat produk belum punya foto.
 * Duotone gradient per-kategori + monogram inisial + icon makanan.
 * Output inline SVG (mengikuti warna parent via CSS bila perlu).
 */
function product_art(string $name, string $category = '', string $class = '', int $view = 400): string {
    $palettes = [
        'Geprek Original' => ['#fca17a', '#e84b23'],
        'Geprek Level'    => ['#ff8a5c', '#c23a14'],
        'Nasi Geprek'     => ['#ffd98a', '#e8890c'],
        'Minuman'         => ['#b7e0d2', '#1f7a62'],
        'Side Dish'       => ['#e9dcae', '#8a7c3a'],
    ];
    [$c1, $c2] = $palettes[$category] ?? ['#f6e3c7', '#e8890c'];
    $uid  = substr(md5($name . $category), 0, 10);
    $init = $name !== '' ? mb_strtoupper(mb_substr($name, 0, 1)) : 'G';
    $r = $view / 2;

    $glyph = match ($category) {
        'Minuman'   => '<path d="M8 4h8M10 4v3l-3.5 9a2 2 0 0 0 1.9 2.7h7.2a2 2 0 0 0 1.9-2.7L14 7V4"/><path d="M7.5 12h9"/>',
        'Side Dish' => '<circle cx="9.5" cy="9" r="1.3"/><circle cx="14.5" cy="8" r="1.3"/><circle cx="12" cy="13" r="1.3"/><path d="M6 17c2 1.4 4 1.4 6 0s4-1.4 6 0"/>',
        'Geprek Level' => '<path d="M12 2c.6 3-1.2 4.4-2.2 6-2.9 4.6-.4 9 2.2 9 4 0 6-2.6 6-5.6 0-2.4-1.3-3.9-2.4-5.4.1 1.9-1.3 2.9-2.3 3.3.6-3-1.3-7.3 1.3-7.3z"/>',
        default     => '<path d="M7.6 9A5.5 5.5 0 0 0 8 10a5 5 0 1 1 4.5 7c-1.6 0-3-.7-4-1.9"/><path d="M6 3l-1 3M9 2l-1.5 3"/>',
    };

    $dots = '';
    foreach ([['0.18','0.16'],['0.84','0.12'],['0.78','0.82'],['0.14','0.78'],['0.55','0.93']] as $i => [$dx, $dy]) {
        $seed = hexdec(substr($uid, $i, 2));
        $dots .= '<circle cx="' . round($view * ($dx + (($seed % 40) - 20) / 240)) . '" cy="' . round($view * ($dy + ((($seed >> 2) % 40) - 20) / 240)) . '" r="' . max(2, $view * 0.005) . '"/>';
    }

    return '<svg class="product-art ' . e($class) . '" viewBox="0 0 ' . $view . ' ' . $view . '" role="img" aria-label="' . e($name ?: 'Geprek Geh') . '" xmlns="http://www.w3.org/2000/svg">'
        . '<defs><linearGradient id="pa-' . $uid . '" x1="0" y1="0" x2="1" y2="1">'
        . '<stop offset="0" stop-color="' . $c1 . '"/><stop offset="1" stop-color="' . $c2 . '"/></linearGradient></defs>'
        . '<rect width="' . $view . '" height="' . $view . '" rx="' . round($view * 0.11) . '" fill="url(#pa-' . $uid . ')" opacity="0.16"/>'
        . '<g fill="#fff" opacity="0.30">' . $dots . '</g>'
        . '<circle cx="' . $r . '" cy="' . $r . '" r="' . round($view * 0.30) . '" fill="#fffdf9" stroke="rgba(29,26,21,0.08)"/>'
        . '<circle cx="' . $r . '" cy="' . $r . '" r="' . round($view * 0.24) . '" fill="none" stroke="rgba(29,26,21,0.05)" stroke-dasharray="1.5 5"/>'
        . '<text x="50%" y="53%" text-anchor="middle" dominant-baseline="middle" font-family="Fraunces, Georgia, serif" font-size="' . round($view * 0.30) . '" font-weight="600" fill="#1d1a15" opacity="0.92">' . e($init) . '</text>'
        . '<circle cx="' . $r . '" cy="' . round($view * 0.78) . '" r="' . round($view * 0.10) . '" fill="#1d1a15"/>'
        . '<g transform="translate(' . round($view * 0.465) . ' ' . round($view * 0.745) . ') scale(' . round($view * 0.045 / 12, 3) . ')"><svg x="0" y="0" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fffdf9" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">' . $glyph . '</svg></g>'
        . '</svg>';
}

/** Isi keranjang global (drawer/warna badge navigasi). */
function cart_summary(): array {
    $db = Database::getInstance();
    $where = Auth::check() ? 'user_id' : 'session_id';
    $val   = Auth::check() ? Auth::id() : session_id();
    $items = $db->fetchAll(
        "SELECT ct.id, ct.quantity, p.name, p.slug, p.price, p.image, p.stock, c.name AS category_name
         FROM cart ct JOIN products p ON ct.product_id = p.id
         JOIN categories c ON p.category_id = c.id
         WHERE ct.{$where} = ? ORDER BY ct.created_at",
        [$val]
    );
    $app = require __DIR__ . '/../config/app.php';
    $subtotal = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $items));
    $tax = (int) ($subtotal * $app['tax_rate']);
    return [
        'items'       => $items,
        'count'       => array_sum(array_map(fn($i) => $i['quantity'], $items)),
        'subtotal'    => $subtotal,
        'tax'         => $tax,
        'shipping'    => $app['shipping'],
        'grand_total' => $subtotal + $tax + $app['shipping'],
    ];
}
