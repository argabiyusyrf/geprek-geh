<?php
// Registrasi rute. Dipanggil dari index.php SETELAH config/bootstrap.php,
// yang menyediakan $router di scope pemanggil.

// ─── Public ───────────────────────────────────────
$router->get('/',                          ['HomeController', 'index']);
$router->get('/products',                  ['ProductController', 'index']);
$router->get('/products/{slug}',           ['ProductController', 'show']);
$router->get('/sitemap.xml',               ['SeoController', 'sitemap']);
$router->get('/robots.txt',                ['SeoController', 'robots']);

// ─── Auth ─────────────────────────────────────────
$router->get('/auth/login',                ['AuthController', 'loginForm']);
$router->post('/auth/login',               ['AuthController', 'login']);
$router->get('/auth/register',             ['AuthController', 'registerForm']);
$router->post('/auth/register',            ['AuthController', 'register']);
$router->get('/auth/forgot',               ['PasswordResetController', 'requestForm']);
$router->post('/auth/forgot',              ['PasswordResetController', 'request']);
$router->get('/auth/reset',                ['PasswordResetController', 'resetForm']);
$router->post('/auth/reset',               ['PasswordResetController', 'reset']);
// Recovery via kata kunci (tanpa email) — langsung form ubah password
$router->get('/auth/recovery',             ['RecoveryController', 'changeForm']);
$router->post('/auth/recovery',            ['RecoveryController', 'change']);
$router->post('/auth/logout',               ['AuthController', 'logout']);
$router->get('/auth/2fa',                  ['AuthController', 'twoFactorForm']);
$router->post('/auth/2fa',                 ['AuthController', 'twoFactorSubmit']);

// ─── Account / Profile ────────────────────────────
$router->get('/account',                   ['ProfileController', 'index']);
$router->post('/account',                  ['ProfileController', 'update']);
$router->post('/account/password',         ['ProfileController', 'changePassword']);
$router->post('/account/email',            ['ProfileController', 'changeEmail']);
$router->post('/account/2fa/setup',        ['ProfileController', 'twoFactorSetup']);
$router->post('/account/2fa/cancel',       ['ProfileController', 'twoFactorCancel']);
$router->post('/account/2fa/confirm',      ['ProfileController', 'twoFactorConfirm']);
$router->post('/account/2fa/disable',      ['ProfileController', 'twoFactorDisable']);
$router->post('/account/2fa/recovery',     ['ProfileController', 'twoFactorRegenerate']);
$router->post('/account/notifications/read-all',   ['NotificationController', 'readAll']);
$router->post('/account/notifications/{id}/read',   ['NotificationController', 'read']);
$router->get('/account/notifications',       ['NotificationController', 'index']);

// Account — addresses (multi-alamat, drawer)
$router->post('/account/addresses',        ['ProfileController', 'store']);
$router->post('/account/addresses/{id}',   ['ProfileController', 'edit']);
$router->post('/account/addresses/{id}/edit',       ['ProfileController', 'openEdit']);
$router->post('/account/addresses/{id}/set-default', ['ProfileController', 'setDefault']);
$router->post('/account/addresses/{id}/delete',      ['ProfileController', 'delete']);

// Account — sessions (perangkat & keamanan)
$router->post('/account/sessions/{id}/revoke',  ['ProfileController', 'revokeSession']);
$router->post('/account/sessions/revoke-all',   ['ProfileController', 'revokeAllSessions']);

// Account — notifications toggle + delete
$router->post('/account/notifications/toggle', ['ProfileController', 'toggleNotifications']);
$router->post('/account/delete',               ['ProfileController', 'deleteAccount']);

// Account — setup awal (kata kunci + alamat opsional)
$router->get('/account/setup',            ['OnboardingController', 'show']);
$router->post('/account/setup',           ['OnboardingController', 'save']);
$router->post('/account/setup/skip',      ['OnboardingController', 'skip']);

// Account — kata kunci recovery (profil > keamanan)
$router->post('/account/keyword',         ['ProfileController', 'changeKeyword']);

// ─── Cart ─────────────────────────────────────────
$router->get('/cart',                      ['CartController', 'index']);
$router->get('/cart/drawer',              ['CartController', 'drawer']);
$router->post('/cart/add',                ['CartController', 'add']);
$router->post('/cart/update',             ['CartController', 'update']);
$router->post('/cart/remove',             ['CartController', 'remove']);
$router->post('/cart/clear',              ['CartController', 'clear']);

// ─── Reviews ────────────────────────────────────────
$router->post('/reviews',                     ['ReviewController', 'store']);
$router->post('/reviews/{id}/delete',         ['ReviewController', 'delete']);

// ─── Wishlist ──────────────────────────────────────────
$router->get('/wishlist',                  ['WishlistController', 'index']);
$router->post('/wishlist/{id}/toggle',     ['WishlistController', 'toggle']);

// ─── Promo ──────────────────────────────────────────
$router->post('/promo/apply',                 ['PromoController', 'apply']);
$router->post('/promo/remove',                ['PromoController', 'remove']);

// ─── Legal / Static pages ───────────────────────────
$router->get('/pages/terms',                  ['PageController', 'terms']);
$router->get('/pages/privacy',                ['PageController', 'privacy']);

// ─── Checkout & Orders ───────────────────────────
$router->get('/checkout',                  ['CheckoutController', 'index']);
$router->post('/checkout',                 ['CheckoutController', 'process']);
$router->get('/orders',                    ['OrderController', 'index']);
$router->get('/orders/{id}',               ['OrderController', 'show']);
$router->get('/orders/{id}/payment-proof', ['OrderController', 'paymentProof']);
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
$router->post('/admin/categories/{id}',    ['Admin\CategoryController', 'update']);
$router->post('/admin/categories/{id}/delete',['Admin\CategoryController', 'delete']);
$router->get('/admin/orders',              ['Admin\OrderController', 'index']);
$router->get('/admin/orders/{id}',         ['Admin\OrderController', 'show']);
$router->get('/admin/orders/{id}/print',   ['Admin\OrderController', 'printOrder']);
$router->post('/admin/orders/{id}/status', ['Admin\OrderController', 'updateStatus']);
$router->post('/admin/orders/{id}/verify-payment', ['Admin\OrderController', 'verifyPayment']);
$router->get('/admin/users',               ['Admin\UserController', 'index']);
$router->get('/admin/users/{id}',          ['Admin\UserController', 'show']);
$router->post('/admin/users',              ['Admin\UserController', 'store']);
$router->post('/admin/users/{id}',         ['Admin\UserController', 'update']);
$router->post('/admin/users/{id}/block',   ['Admin\UserController', 'block']);
$router->post('/admin/users/{id}/delete',  ['Admin\UserController', 'destroy']);

$router->get('/admin/promos',              ['Admin\PromoController', 'index']);
$router->post('/admin/promos',             ['Admin\PromoController', 'store']);
$router->post('/admin/promos/{id}',        ['Admin\PromoController', 'update']);
$router->post('/admin/promos/{id}/toggle', ['Admin\PromoController', 'toggle']);
$router->post('/admin/promos/{id}/delete', ['Admin\PromoController', 'delete']);

$router->get('/admin/reports',               ['Admin\ReportController', 'index']);
$router->get('/admin/reports/export',         ['Admin\ReportController', 'export']);
$router->get('/admin/reports/print',          ['Admin\ReportController', 'printPage']);
$router->get('/admin/stock',                  ['Admin\StockController', 'index']);
$router->get('/admin/stock/{id}',             ['Admin\StockController', 'show']);
$router->post('/admin/stock/{id}/restock',    ['Admin\StockController', 'restock']);
$router->get('/admin/reviews',                ['Admin\ReviewController', 'index']);
$router->post('/admin/reviews/{id}/toggle',   ['Admin\ReviewController', 'toggle']);
$router->post('/admin/reviews/{id}/delete',   ['Admin\ReviewController', 'delete']);
$router->get('/admin/notifications',          ['Admin\NotificationController', 'index']);
$router->post('/admin/notifications',         ['Admin\NotificationController', 'send']);
$router->get('/admin/payments',               ['Admin\PaymentController', 'index']);
$router->post('/admin/payments',              ['Admin\PaymentController', 'store']);
$router->post('/admin/payments/{id}',         ['Admin\PaymentController', 'update']);
$router->post('/admin/payments/{id}/toggle',  ['Admin\PaymentController', 'toggle']);
$router->post('/admin/payments/{id}/delete',  ['Admin\PaymentController', 'delete']);
$router->get('/admin/settings',              ['Admin\SettingsController', 'index']);
$router->post('/admin/settings',             ['Admin\SettingsController', 'save']);
$router->get('/admin/backup',                ['Admin\BackupController', 'index']);
$router->post('/admin/backup/run',           ['Admin\BackupController', 'run']);
$router->get('/admin/backup/{name}/download',['Admin\BackupController', 'download']);
$router->post('/admin/backup/{name}/delete', ['Admin\BackupController', 'delete']);

return $router;