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
    if (isset($map['bank_name']))           $app['payment']['bank']['name'] = $map['bank_name'];
    if (isset($map['bank_number']))         $app['payment']['bank']['number'] = $map['bank_number'];
    if (isset($map['bank_holder']))         $app['payment']['bank']['holder'] = $map['bank_holder'];
    if (isset($map['ewallet_name']))        $app['payment']['ewallet']['name'] = $map['ewallet_name'];
    if (isset($map['ewallet_number']))      $app['payment']['ewallet']['number'] = $map['ewallet_number'];
    if (isset($map['ewallet_holder']))      $app['payment']['ewallet']['holder'] = $map['ewallet_holder'];
} catch (Throwable $e) {
    // DB unavailable — use hardcoded defaults
}

$_app_cache = $app;
return $app;
