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
    gg_redirect($url);
}

/**
 * Base path mount app (mis. "/geprek-geh"), atau "" saat di root.
 * Didefinisikan di config/bootstrap.php dari env GG_BASE_PATH.
 */
function gg_base(): string {
    return defined('GG_BASE_PATH') && GG_BASE_PATH !== '' ? '/' . GG_BASE_PATH : '';
}

/**
 * Token URL root-absolute milik aplikasi (rute + aset statis).
 * Dipakai gg_url_rewrite() agar aman: hanya literal quote+path yang diganti,
 * jadi markup seperti "/><circle" tidak pernah tersentuh.
 */
function gg_base_root_tokens(): array {
    return [
        '/account', '/admin', '/auth', '/cart', '/checkout', '/orders',
        '/pages', '/products', '/promo', '/reviews', '/wishlist',
        '/sitemap.xml', '/robots.txt', '/favicon.ico',
        '/public', '/vendor', '/assets',
        '/nama-produk', '/nama-kategori',
    ];
}

/**
 * Tulis ulang URL root-absolute ("/x" → "/base/x") di output HTML/JS
 * saat app di-mount di sub-path. Tanpa base (GG_BASE_PATH kosong):
 * fungsi no-op dan tidak mengubah apa pun.
 */
function gg_url_rewrite(string $html): string {
    $base = gg_base();
    if ($base === '') return $html;

    $html = strtr($html, [
        'href="/"'    => 'href="' . $base . '/"',
        "href='/'"    => "href='" . $base . "/'",
        'action="/"'  => 'action="' . $base . '/"',
        "action='/'"  => "action='" . $base . "/'",
        'fetch("/")'  => 'fetch("' . $base . '/")',
        "fetch('/')"  => "fetch('" . $base . "/')",
    ]);

    foreach (gg_base_root_tokens() as $token) {
        $with = $base . $token;
        $html = str_replace('"' . $token, '"' . $with, $html);
        $html = str_replace("'" . $token, "'" . $with, $html);
    }

    return $html;
}

/** redirect() dengan kesadaran base path: prefix "/" → base + "/". */
function gg_redirect($url) {
    $base = gg_base();
    if ($base !== '' && is_string($url)
        && str_starts_with($url, '/') && !str_starts_with($url, '//')) {
        $url = $base . $url;
    }
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

/**
 * Simpan error validasi + input lama agar drawer bisa dibuka ulang
 * dengan nilai dan pesan error per-field (redirect-only flow).
 */
function form_stash(array $errors = [], array $old = []): void {
    $_SESSION['form_errors'] = $errors;
    $_SESSION['form_old']    = $old;
}

function form_errors(): ?array {
    $e = $_SESSION['form_errors'] ?? null;
    unset($_SESSION['form_errors']);
    return $e;
}

function form_old(): ?array {
    $o = $_SESSION['form_old'] ?? null;
    unset($_SESSION['form_old']);
    return $o;
}

/** Nilai input: prioritas input lama (error restore) lalu data sumber/db. */
function fval(?array $old, array $src, string $key, $def = '') {
    if ($old !== null && array_key_exists($key, $old)) return $old[$key];
    return $src[$key] ?? $def;
}

/** Batasi konten deskripsi hanya ke tag format dasar (B/I/U) + bersihkan atribut. */
function sanitize_rich_text(?string $html): string {
    $html = trim((string) $html);
    $html = strip_tags($html, '<b><strong><i><em><u>');
    $html = preg_replace('/<(\/?)(?:b|strong|i|em|u)\s[^>]*>/i', '<$1$2>', $html);
    return $html;
}

/** Render deskripsi aman: B/I/U opsional + ganti newline jadi <br>. */
function rich_text(?string $html): string {
    return nl2br(sanitize_rich_text($html));
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

/** Normalisasi nomor HP: buang non-digit, ubah awalan 62 → 0. */
function normalize_phone($phone) {
    $digits = preg_replace('/\D/', '', trim((string) ($phone ?? '')));
    if (str_starts_with($digits, '62')) $digits = '0' . substr($digits, 2);
    return $digits;
}

/** Cek format nomor HP Indonesia valid (08 + 8-11 digit). */
function valid_phone($phone) {
    return preg_match('/^08\d{8,11}$/', normalize_phone($phone)) === 1;
}

/** Alamat tersimpan milik user (default di urutan pertama). */
function user_addresses($userId): array {
    return Database::getInstance()->fetchAll(
        "SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, updated_at DESC",
        [$userId]
    );
}

function order_log($db, $order_id, $actor, $message) {
    $db->insert('order_logs', ['order_id' => $order_id, 'actor' => $actor, 'message' => $message]);
}

/** Item pesanan + info produk/kategori (untuk detail order customer & admin). */
function order_items($order_id): array {
    return Database::getInstance()->fetchAll(
        "SELECT oi.*, p.name, p.image, p.slug, c.name AS category_name
         FROM order_items oi JOIN products p ON oi.product_id = p.id
         LEFT JOIN categories c ON p.category_id = c.id
         WHERE oi.order_id = ?",
        [$order_id]
    );
}

// order_restore_stock() memakai versi lanjutan di bagian bawah file
// (mencatat pergerakan stok otomatis ke stock_movements).

function wa_link($number, string $text = '') {
    $n = preg_replace('/\D/', '', $number ?? '');
    if (str_starts_with($n, '0')) $n = '62' . substr($n, 1);
    if ($text === '') return 'https://wa.me/' . $n;
    return 'https://wa.me/' . $n . '?text=' . rawurlencode($text);
}

/** Template pesan WhatsApp dari pengaturan (cache statis). Placeholder: {nama}, {invoice}. */
function wa_template(string $key, array $vars = []): string {
    static $templates = null;
    if ($templates === null) {
        $templates = [];
        try {
            $rows = Database::getInstance()->fetchAll(
                "SELECT skey, svalue FROM toko_settings WHERE skey LIKE 'wa_template_%'"
            );
            foreach ($rows as $r) $templates[$r['skey']] = (string) $r['svalue'];
        } catch (Throwable $e) {
            // tabel belum ada
        }
    }
    $text = $templates[$key] ?? '';
    if ($text === '') return '';
    foreach ($vars as $k => $v) {
        $text = str_replace('{' . $k . '}', (string) $v, $text);
    }
    return $text;
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
    [$whereCol, $whereVal] = cart_where();
    $items = $db->fetchAll(
        "SELECT ct.id, ct.quantity, p.name, p.slug, p.price, p.image, p.stock, c.name AS category_name
         FROM cart ct JOIN products p ON ct.product_id = p.id
         JOIN categories c ON p.category_id = c.id
         WHERE ct.{$whereCol} = ? ORDER BY ct.created_at",
        [$whereVal]
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

/** Hitung grand_total dari data order (3NF — tidak ada kolom grand_total). */
function grand_total(array $order): int {
    return (int) ($order['total'] ?? 0)
         - (int) ($order['discount'] ?? 0)
         + (int) ($order['shipping_cost'] ?? 0)
         + (int) ($order['tax'] ?? 0);
}

/** Kolom & nilai where clause untuk cart (user login vs guest session). */
function cart_where(): array {
    if (Auth::check()) {
        $db = Database::getInstance();
        $valid = $db->fetchOne("SELECT id FROM users WHERE id = ?", [Auth::id()]);
        if ($valid) {
            return ['user_id', Auth::id()];
        }
        unset($_SESSION['user_id'], $_SESSION['role'], $_SESSION['user_name'], $_SESSION['user_email']);
    }
    return ['session_id', session_id()];
}

/** Hitung ringkasan order (subtotal, tax, diskon, grand total). */
function calculateOrderSummary(array $items, ?array $promo = null): array {
    $app = require __DIR__ . '/../config/app.php';
    $subtotal = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $items));
    $tax = (int) ($subtotal * $app['tax_rate']);
    $shipping = $app['shipping'];
    $discount = 0;
    $promo_label = '';
    if ($promo && $subtotal > 0) {
        $d = PromoController::calcDiscount($promo, $subtotal);
        $discount = $d['discount'];
        $promo_label = $d['label'];
    }
    return [
        'subtotal'    => $subtotal,
        'tax'         => $tax,
        'shipping'    => $shipping,
        'discount'    => $discount,
        'promo_label' => $promo_label,
        'grand_total' => max(0, $subtotal - $discount + $tax + $shipping),
    ];
}

// ─────────── Metode pembayaran multi (payment_methods) ───────────

/** Ambil detail metode pembayaran dari kode-nya (cache statis per-request). */
function payment_method_details(?string $code): ?array {
    if ($code === null || $code === '') return null;
    static $methods = null;
    if ($methods === null) {
        $methods = [];
        foreach (Database::getInstance()->fetchAll("SELECT * FROM payment_methods") as $m) {
            $methods[$m['code']] = $m;
        }
    }
    return $methods[$code] ?? null;
}

/** Konfigurasi gateway pembayaran (QRIS) dari toko_settings (cache statis). */
function payment_gateway(): array {
    static $gw = null;
    if ($gw !== null) return $gw;
    $default = ['type' => 'none', 'label' => 'QRIS', 'number' => ''];
    try {
        $rows = Database::getInstance()->fetchAll(
            "SELECT skey, svalue FROM toko_settings WHERE skey LIKE 'payment_gateway_%'"
        );
        foreach ($rows as $r) {
            $key = substr($r['skey'], strlen('payment_gateway_'));
            if ($key === 'type' || $key === 'number' || $key === 'label') {
                $default[$key] = $r['svalue'];
            }
        }
    } catch (Throwable $e) {
        // table absent — pakai default
    }
    $gw = $default;
    return $gw;
}

/** Apakah gateway QRIS aktif? */
function qris_payment_enabled(): bool {
    return payment_gateway()['type'] === 'qris';
}

/** Label ramah-manusia untuk kode metode (fallback ke metode legacy). */
function payment_method_label(?string $code): string {
    $m = payment_method_details($code);
    if ($m) {
        if ($m['type'] === 'ewallet') return 'E-Wallet ' . $m['name'];
        return 'Transfer ' . $m['name'];
    }
    if ($code === 'qris') {
        $gw = payment_gateway();
        return 'QRIS' . ($gw['label'] !== '' ? ' (' . $gw['label'] . ')' : '');
    }
    return [
        'transfer' => 'Transfer Bank',
        'cod'      => 'Bayar di Tempat (COD)',
        'ewallet'  => 'E-Wallet (ShopeePay)',
    ][$code ?? ''] ?? ucfirst((string) $code);
}

/** Tipe metode utk keputusan alur bayar: bank / ewallet / cod / qris / unknown. */
function payment_method_type(?string $code): string {
    $m = payment_method_details($code);
    if ($m) return $m['type'];
    if ($code === 'cod') return 'cod';
    if ($code === 'qris') return 'qris';
    if ($code === 'transfer') return 'bank';
    if ($code === 'ewallet') return 'ewallet';
    return 'unknown';
}

/** Apakah metode ini butuh upload bukti bayar? (semua kecuali COD). */
function payment_requires_proof(?string $code): bool {
    return payment_method_type($code) !== 'cod';
}

/** Catat pergerakan stok produk ke tabel stock_movements. */
function stock_log(int $productId, int $qtyChange, ?string $note = null, ?int $userId = null): void {
    $db = Database::getInstance();
    $db->insert('stock_movements', [
        'product_id' => $productId,
        'qty_change' => $qtyChange,
        'note'       => $note !== null && $note !== '' ? $note : null,
        'user_id'    => $userId,
    ]);
}

/** Perbaiki order_restore_stock: kembalikan stok + log otomatis ke stock_movements. */
function order_restore_stock($db, $order_id) {
    $order = $db->fetchOne("SELECT id, invoice_no FROM orders WHERE id = ?", [$order_id]);
    $label = $order ? "pembatalan #{$order['invoice_no']}" : 'pembatalan';
    $items = $db->fetchAll("SELECT product_id, quantity FROM order_items WHERE order_id = ?", [$order_id]);
    foreach ($items as $item) {
        $db->query("UPDATE products SET stock = stock + ? WHERE id = ?", [$item['quantity'], $item['product_id']]);
        stock_log((int) $item['product_id'], (int) $item['quantity'], $label, null);
    }
}

/** Kirim email perubahan status order (best-effort, ikuti preferensi notifikasi user). */
function order_status_email($userId, string $invoiceNo, string $statusLabel, string $message): void {
    try {
        $customer = Database::getInstance()->fetchOne(
            "SELECT email, name, notify_email FROM users WHERE id = ?",
            [$userId]
        );
        if ($customer && !empty($customer['email']) && (int) ($customer['notify_email'] ?? 1) === 1) {
            Mail::orderStatusChanged($customer['email'], $customer['name'], $invoiceNo, $statusLabel, $message);
        }
    } catch (Exception $e) {
        error_log('[mail] order status email failed: ' . $e->getMessage());
    }
}

// ─────────── Wishlist ───────────

/** Set id produk di wishlist user yg login (cache statis per-request). */
function wishlist_ids(): array {
    static $ids = null;
    if ($ids !== null) return $ids;
    $ids = [];
    if (isset($_SESSION['user_id'])) {
        try {
            $rows = Database::getInstance()->fetchAll(
                "SELECT product_id FROM wishlists WHERE user_id = ?",
                [$_SESSION['user_id']]
            );
            foreach ($rows as $r) $ids[(int) $r['product_id']] = true;
        } catch (Throwable $e) {
            // tabel belum ada
        }
    }
    return $ids;
}

// ─────────── Rendering ───────────

/**
 * Render view dengan layout yang sesuai sambil membagikan variabel scope
 * controller ke view (pengganti manual require header + view + footer).
 *
 * Layout otomatis dari prefix view: `admin/` → admin, `auth/` → auth, lainnya → site.
 * View `account/setup` (onboarding) memakai layout auth — lewati $layout eksplisit.
 */
function render(string $__gg_view, array $__gg_vars = [], string $__gg_layout = 'auto'): void {
    if ($__gg_layout === 'auto') {
        if (str_starts_with($__gg_view, 'admin/')) {
            $__gg_layout = 'admin';
        } elseif (str_starts_with($__gg_view, 'auth/')) {
            $__gg_layout = 'auth';
        } else {
            $__gg_layout = 'site';
        }
    }

    extract($__gg_vars, EXTR_SKIP);

    $__gg_base = dirname(__DIR__) . '/views';
    switch ($__gg_layout) {
        case 'admin':
            require $__gg_base . '/layouts/admin-header.php';
            require $__gg_base . '/' . $__gg_view . '.php';
            require $__gg_base . '/layouts/admin-footer.php';
            break;
        case 'auth':
            require $__gg_base . '/layouts/auth-header.php';
            require $__gg_base . '/' . $__gg_view . '.php';
            require $__gg_base . '/layouts/auth-footer.php';
            break;
        default:
            require $__gg_base . '/layouts/header.php';
            require $__gg_base . '/' . $__gg_view . '.php';
            require $__gg_base . '/layouts/footer.php';
    }
}
