<?php
// Bootstrap aplikasi. Dipanggil dari index.php (satu-satunya entrypoint).
// Berisi: passthrough file statis, sesi, handler error, require core,
// autoload controller, remember-me, dan instansiasi Router.
// Mengembalikan instance Router — route diregistrasi di config/routes.php.

$__root = dirname(__DIR__);

// Passthrough file statis untuk built-in server (php -S host:port index.php).
// Di Apache ini sudah ditangani .htaccess (!-f / !-d), blok ini dormant.
$_zp = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '');
if (str_starts_with($_zp, '/') && strpos($_zp, '..') === false) {
    $_zf = $__root . $_zp;
    if (is_file($_zf)) {
        $_zm = [
            'css' => 'text/css', 'js' => 'application/javascript', 'mjs' => 'application/javascript',
            'json' => 'application/json', 'png' => 'image/png', 'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg', 'gif' => 'image/gif', 'webp' => 'image/webp',
            'avif' => 'image/avif', 'svg' => 'image/svg+xml', 'ico' => 'image/x-icon',
            'woff' => 'font/woff', 'woff2' => 'font/woff2', 'ttf' => 'font/ttf',
            'otf' => 'font/otf', 'mp4' => 'video/mp4', 'webm' => 'video/webm',
            'pdf' => 'application/pdf', 'txt' => 'text/plain',
        ];
        $_ze = strtolower(pathinfo($_zf, PATHINFO_EXTENSION));
        if (isset($_zm[$_ze])) {
            header('Content-Type: ' . $_zm[$_ze]);
            header('Content-Length: ' . filesize($_zf));
            readfile($_zf);
            exit;
        }
    }
}
unset($_zp, $_zf, $_zm, $_ze);

// Workaround HTTP server tertentu: jika fastcgi/rewrite fallback memotong
// query string (REQUEST_URI masih memuat "?", tapi QUERY_STRING kosong),
// pulihkan parameter asli ke $_GET.
if (empty($_SERVER['QUERY_STRING']) && isset($_SERVER['REQUEST_URI'])
    && strpos($_SERVER['REQUEST_URI'], '?') !== false) {
    $__q = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY) ?? '';
    parse_str($__q, $_GET);
}
unset($__q);

// Derive url untuk built-in server (php -S host:port index.php / router.php).
// Di Apache, .htaccess sudah mengirim ?url=, jadi blok ini hanya mengisi bila kosong.
if (empty($_GET['url']) && isset($_SERVER['REQUEST_URI'])) {
    $__u = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '');
    $_GET['url'] = trim($__u, '/');
}
unset($__u);

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
]);
session_start();

set_exception_handler(function (Throwable $e) use ($__root) {
    $msg = '[FATAL] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();
    error_log($msg);
    @file_put_contents($__root . '/logs/error.log', date('Y-m-d H:i:s') . ' ' . $msg . "\n", FILE_APPEND);
    $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    if ($isAjax) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'Terjadi kesalahan server. Silakan coba lagi.']);
    } else {
        http_response_code(500);
        if (ob_get_level()) ob_end_clean();
        require $__root . '/views/layouts/500.php';
    }
    exit;
});

register_shutdown_function(function () use ($__root) {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $msg = '[SHUTDOWN] ' . $error['message'] . ' in ' . $error['file'] . ':' . $error['line'];
        error_log($msg);
        @file_put_contents($__root . '/logs/error.log', date('Y-m-d H:i:s') . ' ' . $msg . "\n", FILE_APPEND);
    }
});

require_once $__root . '/core/Database.php';
require_once $__root . '/core/Router.php';
require_once $__root . '/core/Auth.php';
require_once $__root . '/core/Totp.php';
require_once $__root . '/core/Mail.php';
require_once $__root . '/core/RateLimiter.php';
require_once $__root . '/core/ProductRepo.php';
require_once $__root . '/core/helpers.php';

// Remember-me: pulihkan sesi dari cookie persist tanpa perlu login ulang.
if (!isset($_SESSION['user_id'])) {
    Auth::autoLoginFromRemember();
}

spl_autoload_register(function ($class) use ($__root) {
    $prefix = 'Admin\\';
    if (strncmp($class, $prefix, strlen($prefix)) === 0) {
        $file = $__root . '/controllers/admin/' . str_replace($prefix, '', $class) . '.php';
    } else {
        $file = $__root . '/controllers/' . $class . '.php';
    }
    if (is_file($file)) {
        require_once $file;
    }
});

$app = require $__root . '/config/app.php';

unset($__root);

return new Router();