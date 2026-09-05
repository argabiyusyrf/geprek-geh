<?php
// Passthrough file statis untuk built-in server (php -S host:port index.php).
// Di Apache ini sudah ditangani .htaccess (!-f / !-d), blok ini dormant.
$__path = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '');
if (str_starts_with($__path, '/geprek-geh/') && strpos($__path, '..') === false) {
    $__file = __DIR__ . '/' . substr($__path, strlen('/geprek-geh/'));
    if (is_file($__file)) {
        $__mime = [
            'css' => 'text/css', 'js' => 'application/javascript', 'mjs' => 'application/javascript',
            'json' => 'application/json', 'png' => 'image/png', 'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg', 'gif' => 'image/gif', 'webp' => 'image/webp',
            'avif' => 'image/avif', 'svg' => 'image/svg+xml', 'ico' => 'image/x-icon',
            'woff' => 'font/woff', 'woff2' => 'font/woff2', 'ttf' => 'font/ttf',
            'otf' => 'font/otf', 'mp4' => 'video/mp4', 'webm' => 'video/webm',
            'pdf' => 'application/pdf', 'txt' => 'text/plain',
        ];
        $__ext = strtolower(pathinfo($__file, PATHINFO_EXTENSION));
        if (isset($__mime[$__ext])) {
            header('Content-Type: ' . $__mime[$__ext]);
            header('Content-Length: ' . filesize($__file));
            readfile($__file);
            exit;
        }
    }
}

// Derive url untuk built-in server (php -S host:port index.php / router.php).
// Di Apache, .htaccess sudah mengirim ?url=, jadi blok ini hanya mengisi bila kosong.
if (empty($_GET['url']) && isset($_SERVER['REQUEST_URI'])) {
    $__u = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '');
    if (str_starts_with($__u, '/geprek-geh')) {
        $__u = substr($__u, strlen('/geprek-geh'));
    }
    $_GET['url'] = trim($__u, '/');
}

session_start();

require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/core/Auth.php';
require_once __DIR__ . '/core/Totp.php';
require_once __DIR__ . '/core/helpers.php';

spl_autoload_register(function ($class) {
    $prefix = 'Admin\\';
    if (strncmp($class, $prefix, strlen($prefix)) === 0) {
        $file = __DIR__ . '/controllers/admin/' . str_replace($prefix, '', $class) . '.php';
    } else {
        $file = __DIR__ . '/controllers/' . $class . '.php';
    }
    if (is_file($file)) {
        require_once $file;
    }
});

$app = require __DIR__ . '/config/app.php';

$router = new Router();

// ─── Public ───────────────────────────────────────
$router->get('/',                          ['HomeController', 'index']);
$router->get('/products',                  ['ProductController', 'index']);
$router->get('/products/{slug}',           ['ProductController', 'show']);

// ─── Auth ─────────────────────────────────────────
$router->get('/auth/login',                ['AuthController', 'loginForm']);
$router->post('/auth/login',               ['AuthController', 'login']);
$router->get('/auth/register',             ['AuthController', 'registerForm']);
$router->post('/auth/register',            ['AuthController', 'register']);
$router->get('/auth/logout',               ['AuthController', 'logout']);
$router->get('/auth/2fa',                  ['AuthController', 'twoFactorForm']);
$router->post('/auth/2fa',                 ['AuthController', 'twoFactorSubmit']);

// ─── Account / Profile ────────────────────────────
$router->get('/account',                   ['ProfileController', 'index']);
$router->post('/account',                  ['ProfileController', 'update']);
$router->post('/account/password',         ['ProfileController', 'changePassword']);
$router->post('/account/2fa/setup',        ['ProfileController', 'twoFactorSetup']);
$router->post('/account/2fa/cancel',       ['ProfileController', 'twoFactorCancel']);
$router->post('/account/2fa/confirm',      ['ProfileController', 'twoFactorConfirm']);
$router->post('/account/2fa/disable',      ['ProfileController', 'twoFactorDisable']);
$router->post('/account/2fa/recovery',     ['ProfileController', 'twoFactorRegenerate']);
$router->post('/account/notifications/read-all',   ['NotificationController', 'readAll']);
$router->post('/account/notifications/{id}/read',   ['NotificationController', 'read']);

// Account — addresses (multi-alamat, drawer)
$router->post('/account/addresses',        ['ProfileController', 'store']);
$router->post('/account/addresses/{id}',   ['ProfileController', 'edit']);
$router->post('/account/addresses/{id}/edit',       ['ProfileController', 'openEdit']);
$router->post('/account/addresses/{id}/set-default', ['ProfileController', 'setDefault']);
$router->post('/account/addresses/{id}/delete',      ['ProfileController', 'delete']);

// ─── Cart ─────────────────────────────────────────
$router->get('/cart',                      ['CartController', 'index']);
$router->post('/cart/add',                 ['CartController', 'add']);
$router->post('/cart/update',              ['CartController', 'update']);
$router->post('/cart/remove',              ['CartController', 'remove']);
$router->post('/cart/clear',               ['CartController', 'clear']);

// ─── Checkout & Orders ───────────────────────────
$router->get('/checkout',                  ['CheckoutController', 'index']);
$router->post('/checkout',                 ['CheckoutController', 'process']);
$router->get('/orders',                    ['OrderController', 'index']);
$router->get('/orders/{id}',               ['OrderController', 'show']);
$router->post('/orders/{id}/upload-proof', ['OrderController', 'uploadProof']);
$router->post('/orders/{id}/cancel',       ['OrderController', 'cancel']);
$router->post('/orders/{id}/receive',      ['OrderController', 'receive']);
$router->post('/orders/{id}/reorder',      ['OrderController', 'reorder']);

// ─── Admin ────────────────────────────────────────
$router->get('/admin',                     ['Admin\DashboardController', 'index']);
$router->get('/admin/products',            ['Admin\ProductController', 'index']);
$router->get('/admin/products/create',     ['Admin\ProductController', 'create']);
$router->post('/admin/products',           ['Admin\ProductController', 'store']);
$router->get('/admin/products/{id}/edit',  ['Admin\ProductController', 'edit']);
$router->post('/admin/products/{id}',      ['Admin\ProductController', 'update']);
$router->post('/admin/products/{id}/delete',['Admin\ProductController', 'delete']);
$router->get('/admin/categories',          ['Admin\CategoryController', 'index']);
$router->post('/admin/categories',         ['Admin\CategoryController', 'store']);
$router->post('/admin/categories/{id}/delete',['Admin\CategoryController', 'delete']);
$router->get('/admin/orders',              ['Admin\OrderController', 'index']);
$router->get('/admin/orders/{id}',         ['Admin\OrderController', 'show']);
$router->post('/admin/orders/{id}/status', ['Admin\OrderController', 'updateStatus']);
$router->post('/admin/orders/{id}/verify-payment', ['Admin\OrderController', 'verifyPayment']);
$router->get('/admin/users',               ['Admin\UserController', 'index']);

$router->dispatch();
