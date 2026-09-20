<?php
static $_app_cache = null;
if ($_app_cache !== null) return $_app_cache;

$app = [
    'name'     => 'Geprek Geh',
    'tagline'  => 'Geprek Pedas Nikmat',
    'url'      => '/geprek-geh',
    'currency' => 'Rp',
    'tax_rate' => 0.11,
    'shipping' => 5000,
    'admin_email' => 'admin@geprekgeh.com',
    'stock_low_threshold' => 10,
    'contacts' => [
        'whatsapp' => '088742674141',
        'hours'    => 'Setiap hari 09.00–21.00 WIB',
    ],
    'payment' => [
        'methods'    => [],
        'bank' => [
            'name'   => 'BNI',
            'number' => '1846757370',
            'holder' => 'GEPREK GEH',
        ],
        'ewallet' => [
            'name'   => 'ShopeePay',
            'number' => '083137274613',
            'holder' => 'GEPREK GEH',
        ],
        'auto_cancel_hours' => 24,
        'gateway' => [
            'type'   => 'none',
            'label'  => 'QRIS',
            'number' => '',
        ],
    ],
];

try {
    $db = \Database::getInstance();
    $rows = $db->fetchAll("SELECT skey, svalue FROM toko_settings");
    $map = [];
    foreach ($rows as $r) { $map[$r['skey']] = $r['svalue']; }

    if (isset($map['shipping']))            $app['shipping'] = (int) $map['shipping'];
    if (isset($map['tax_rate']))            $app['tax_rate'] = (float) $map['tax_rate'];
    if (isset($map['stock_low_threshold'])) $app['stock_low_threshold'] = (int) $map['stock_low_threshold'];
    if (isset($map['contacts_whatsapp']))   $app['contacts']['whatsapp'] = $map['contacts_whatsapp'];
    if (isset($map['contacts_hours']))      $app['contacts']['hours'] = $map['contacts_hours'];
    // Metode pembayaran multi — sumber utama sekarang tabel payment_methods.
    try {
        $methods = $db->fetchAll(
            "SELECT * FROM payment_methods WHERE is_active = 1 ORDER BY sort_order ASC, id ASC"
        );
        if ($methods) {
            $app['payment']['methods'] = $methods;
            // Turunkan konfigurasi legacy bank/ewallet dari metode aktif pertama
            // agar halaman lama (hanya tahu transfer/ewallet) tetap berfungsi.
            $has_bank = false;
            $has_ewallet = false;
            foreach ($methods as $m) {
                $info = ['name' => $m['name'], 'number' => $m['number'], 'holder' => $m['holder']];
                if ($m['type'] === 'bank' && !$has_bank) {
                    $app['payment']['bank'] = $info;
                    $has_bank = true;
                } elseif ($m['type'] === 'ewallet' && !$has_ewallet) {
                    $app['payment']['ewallet'] = $info;
                    $has_ewallet = true;
                }
            }
        }
    } catch (Throwable $e) {
        // tabel belum ada — pakai default
    }
    if (isset($map['auto_cancel_hours']))   $app['payment']['auto_cancel_hours'] = (int) $map['auto_cancel_hours'];
    if (isset($map['payment_gateway_type']))   $app['payment']['gateway']['type'] = $map['payment_gateway_type'];
    if (isset($map['payment_gateway_label']))  $app['payment']['gateway']['label'] = $map['payment_gateway_label'];
    if (isset($map['payment_gateway_number'])) $app['payment']['gateway']['number'] = $map['payment_gateway_number'];
} catch (Throwable $e) {
    // DB unavailable — use hardcoded defaults
}

$_app_cache = $app;
return $app;
