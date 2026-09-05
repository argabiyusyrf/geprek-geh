<?php
// Router untuk php -S — meniru perilaku .htaccess (Apache).
// Jalankan dari root proyek:
//   php -S localhost:8080 router.php
// File statis asli disajikan langsung; yang lain diteruskan ke index.php.

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$base = '/geprek-geh';

$path = (str_starts_with($uri, $base)) ? substr($uri, strlen($base)) : $uri;
if ($path === '') $path = '/';

$mimeTypes = [
    'css'    => 'text/css',
    'js'     => 'application/javascript',
    'mjs'    => 'application/javascript',
    'json'   => 'application/json',
    'png'    => 'image/png',
    'jpg'    => 'image/jpeg',
    'jpeg'   => 'image/jpeg',
    'gif'    => 'image/gif',
    'webp'   => 'image/webp',
    'avif'   => 'image/avif',
    'svg'    => 'image/svg+xml',
    'ico'    => 'image/x-icon',
    'woff'   => 'font/woff',
    'woff2'  => 'font/woff2',
    'ttf'    => 'font/ttf',
    'otf'    => 'font/otf',
    'eot'    => 'application/vnd.ms-fontobject',
    'mp4'    => 'video/mp4',
    'webm'   => 'video/webm',
    'pdf'    => 'application/pdf',
    'txt'    => 'text/plain',
];

$file    = __DIR__ . $path;
$real    = realpath($file);
$rootDir = realpath(__DIR__);

if ($real && $real !== $rootDir && str_starts_with($real, $rootDir . DIRECTORY_SEPARATOR) && is_file($real)) {
    $ext = strtolower(pathinfo($real, PATHINFO_EXTENSION));
    if (isset($mimeTypes[$ext])) {
        header('Content-Type: ' . $mimeTypes[$ext]);
        readfile($real);
        exit;
    }
}

$_GET['url'] = ltrim($path, '/');
require __DIR__ . '/index.php';