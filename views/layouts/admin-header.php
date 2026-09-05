<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — Geprek Geh</title>
    <link rel="stylesheet" href="/geprek-geh/public/fonts/fonts.css">
    <link rel="stylesheet" href="/geprek-geh/vendor/css/lenis.css">
    <link rel="stylesheet" href="/geprek-geh/public/css/style.css?v=20261001">
</head>
<body class="admin-body">

<aside class="sidebar">
    <div class="sidebar-header">
        <a href="/geprek-geh/admin" class="brand">
            <span class="brand-mark">G</span>
            <span class="brand-word">Geprek Geh</span>
        </a>
    </div>
    <nav class="sidebar-nav">
        <a href="/geprek-geh/admin" class="<?= basename($_SERVER['REQUEST_URI']) === 'admin' ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
            Dashboard
        </a>
        <a href="/geprek-geh/admin/products" class="<?= strpos($_SERVER['REQUEST_URI'], 'products') !== false ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l8 4v6c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6z"/><path d="M9 12l2 2 4-4"/></svg>
            Produk
        </a>
        <a href="/geprek-geh/admin/categories" class="<?= strpos($_SERVER['REQUEST_URI'], 'categories') !== false ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7l9-4 9 4v10l-9 4-9-4z"/><path d="M3 7l9 4 9-4M12 11v10"/></svg>
            Kategori
        </a>
        <a href="/geprek-geh/admin/orders" class="<?= strpos($_SERVER['REQUEST_URI'], 'orders') !== false ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            Pesanan
        </a>
        <a href="/geprek-geh/admin/users" class="<?= strpos($_SERVER['REQUEST_URI'], 'users') !== false ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Pengguna
        </a>
        <hr>
        <a href="/geprek-geh/" target="_blank">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
            Lihat Website
        </a>
        <a href="/geprek-geh/auth/logout">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
            Logout
        </a>
    </nav>
</aside>

<nav class="admin-mobile-nav">
    <a href="/geprek-geh/admin" class="<?= basename($_SERVER['REQUEST_URI']) === 'admin' ? 'active' : '' ?>">Dashboard</a>
    <a href="/geprek-geh/admin/products" class="<?= strpos($_SERVER['REQUEST_URI'], 'products') !== false ? 'active' : '' ?>">Produk</a>
    <a href="/geprek-geh/admin/categories" class="<?= strpos($_SERVER['REQUEST_URI'], 'categories') !== false ? 'active' : '' ?>">Kategori</a>
    <a href="/geprek-geh/admin/orders" class="<?= strpos($_SERVER['REQUEST_URI'], 'orders') !== false ? 'active' : '' ?>">Pesanan</a>
    <a href="/geprek-geh/admin/users" class="<?= strpos($_SERVER['REQUEST_URI'], 'users') !== false ? 'active' : '' ?>">Pengguna</a>
</nav>

<div class="admin-main">
    <header class="admin-topbar">
        <div class="admin-title">
            <span class="admin-eyebrow">Geprek Geh · Panel Admin</span>
            <div class="admin-title-row">
                <h2 class="admin-page-title"><?= e($admin_page_title ?? 'Dashboard') ?></h2>
                <span class="badge badge-primary">Admin</span>
            </div>
        </div>
        <div class="admin-topbar-right">
            <div class="account admin-quick" data-account>
                <button class="account-trigger icon-trigger" type="button" aria-expanded="false" aria-haspopup="true" title="Akses Cepat">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8l9-5 9 5v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 21v-8h6v8"/></svg>
                </button>
                <div class="account-menu quick-menu" role="menu">
                    <div class="account-menu-head">
                        <strong>Akses Cepat</strong>
                        <span>Tindakan umum</span>
                    </div>
                    <a href="/geprek-geh/admin/products/create" role="menuitem">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v8M8 12h8"/></svg>
                        Tambah Produk
                    </a>
                    <a href="/geprek-geh/admin/categories" role="menuitem">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7l9-4 9 4v10l-9 4-9-4z"/><path d="M3 7l9 4 9-4M12 11v10"/></svg>
                        Kategori
                    </a>
                    <a href="/geprek-geh/admin/orders" role="menuitem">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        Daftar Pesanan
                    </a>
                    <div class="account-menu-sep"></div>
                    <a href="/geprek-geh/" role="menuitem" target="_blank">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                        Lihat Website
                    </a>
                </div>
            </div>

            <?php $adminNotifs = NotificationController::fetchAll(6); $adminUnread = NotificationController::unreadCount(); ?>
            <div class="account admin-notif" data-account>
                <button class="account-trigger icon-trigger" type="button" aria-expanded="false" aria-haspopup="true" title="Notifikasi">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                    <?php if ($adminUnread > 0): ?>
                        <span class="notif-dot"><?= $adminUnread ?></span>
                    <?php endif; ?>
                </button>
                <div class="account-menu notif-menu" role="menu">
                    <div class="account-menu-head notif-head">
                        <strong>Notifikasi</strong>
                        <span><?= $adminUnread ?> belum dibaca</span>
                    </div>
                    <?php if (empty($adminNotifs)): ?>
                        <div class="notif-foot">Belum ada notifikasi.</div>
                    <?php else: ?>
                        <?php foreach ($adminNotifs as $n): ?>
                        <a href="<?= e($n['link'] ?? '/geprek-geh/admin/orders') ?>" role="menuitem" class="notif-item<?= $n['is_read'] ? '' : ' admin-unread' ?>">
                            <span class="notif-pip <?= $n['type'] === 'payment' ? 'stock' : ($n['type'] === 'order' ? 'pending' : 'user') ?>"></span>
                            <span class="notif-body"><b><?= e($n['title']) ?></b><span><?= e($n['message'] ?? time_ago($n['created_at'])) ?></span></span>
                        </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <div class="account-menu-sep"></div>
                    <div class="notif-foot">
                        <?php if ($adminUnread > 0): ?>
                            <form method="POST" action="/geprek-geh/account/notifications/read-all" class="notif-readall-form">
                                <?= csrf_field() ?>
                                <button type="submit" class="notif-readall">Tandai semua dibaca</button>
                            </form>
                        <?php else: ?>
                            <span>Semua sudah dibaca</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="account admin-user" data-account>
                <button class="account-trigger" type="button" aria-expanded="false" aria-haspopup="true">
                    <span class="account-avatar"><?= e(mb_strtoupper(mb_substr($_SESSION['user_name'], 0, 1))) ?></span>
                    <span class="account-name"><?= e($_SESSION['user_name']) ?></span>
                    <svg class="account-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                </button>
                <div class="account-menu" role="menu">
                    <div class="account-menu-head">
                        <strong><?= e($_SESSION['user_name']) ?></strong>
                        <span><?= e($_SESSION['user_email'] ?? '') ?></span>
                    </div>
                    <a href="/geprek-geh/admin" role="menuitem">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
                        Dashboard
                    </a>
                    <a href="/geprek-geh/" role="menuitem" target="_blank">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                        Lihat Website
                    </a>
                    <div class="account-menu-sep"></div>
                    <a href="/geprek-geh/auth/logout" role="menuitem" class="danger">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                        Keluar
                    </a>
                </div>
            </div>
        </div>
    </header>
    <div class="admin-content">
        <?php $f = flash(); if ($f): ?>
            <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['msg']) ?></div>
        <?php endif; ?>
