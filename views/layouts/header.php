<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#14110C">
    <meta name="description" content="<?= e($page_description ?? 'Geprek Geh — Ayam geprek renyah, sambal level sesuai seleramu, diantar hangat. Pesan online dalam hitungan detik.') ?>">
    <title><?= e($page_title ?? 'Geprek Geh') ?> — Geprek Geh</title>

    <meta property="og:site_name" content="Geprek Geh">
    <meta property="og:title" content="<?= e(($page_title ?? 'Geprek Geh') . ' — Geprek Geh') ?>">
    <meta property="og:description" content="<?= e($page_description ?? 'Ayam geprek renyah, sambal level sesuai seleramu, diantar hangat.') ?>">
    <meta property="og:type" content="<?= e($og_type ?? 'website') ?>">
    <meta property="og:url" content="<?= e('http' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 's' : '') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '/')) ?>">
    <?php if (!empty($og_image)): ?>
        <meta property="og:image" content="<?= e($og_image) ?>">
    <?php endif; ?>

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e(($page_title ?? 'Geprek Geh') . ' — Geprek Geh') ?>">
    <meta name="twitter:description" content="<?= e($page_description ?? 'Ayam geprek renyah, sambal level sesuai seleramu, diantar hangat.') ?>">
    <?php if (!empty($og_image)): ?>
        <meta name="twitter:image" content="<?= e($og_image) ?>">
    <?php endif; ?>

    <link rel="canonical" href="<?= e('http' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 's' : '') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '/')) ?>">
    <link rel="icon" type="image/svg+xml" href="/public/favicon.svg">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="/public/fonts/fonts.css">
    <link rel="stylesheet" href="/vendor/css/lenis.css">
    <link rel="stylesheet" href="/public/css/style.css?v=20260913f">

    <?= SeoController::organizationJsonLd() ?>
    <?= $page_jsonld ?? '' ?>
</head>
<body>
<script>document.documentElement.classList.add('js');</script>

<nav class="island-nav">
    <div class="nav-pill">
        <a href="/" class="brand">
            <span class="brand-mark">G</span>
            <span class="brand-word">Geprek Geh</span>
        </a>

        <?php $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); ?>
        <a href="/products" class="nav-center-link <?= $uri === '/products' || str_starts_with($uri, '/products/') ? 'is-active' : '' ?>">Menu</a>

        <div class="nav-actions">
            <button type="button" class="cart-link cart-trigger icon-trigger" data-open-drawer aria-label="Keranjang">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                <span class="notif-dot" data-cart-count="<?= CartController::count() ?>" <?= CartController::count() > 0 ? '' : 'style="display:none"' ?>><?= CartController::count() ?></span>
            </button>
            <?php if (Auth::check()): ?>
                <a href="/wishlist" class="cart-link icon-trigger wish-nav-link" aria-label="Daftar keinginan">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21.2l7.8-7.8 1-1a5.5 5.5 0 0 0 0-7.8z"/></svg>
                    <?php $wish_count = count(wishlist_ids()); ?>
                    <span class="notif-dot" data-wish-count="<?= $wish_count ?>" <?= $wish_count > 0 ? '' : 'style="display:none"' ?>><?= $wish_count ?></span>
                </a>
            <?php endif; ?>
            <?php if (Auth::check()): ?>
                <?php $notifs = NotificationController::fetchAll(8); $unread = NotificationController::unreadCount(); ?>
                <div class="notif" data-notif>
                    <button type="button" class="notif-trigger" data-notif-trigger aria-label="Notifikasi" aria-expanded="false" aria-haspopup="true">
                        <svg class="notif-bell" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                        <?php if ($unread > 0): ?>
                            <span class="notif-badge" data-notif-count><?= $unread ?></span>
                        <?php endif; ?>
                    </button>
                    <div class="notif-panel" data-notif-panel>
                        <div class="notif-panel-head">
                            <strong>Notifikasi</strong>
                            <?php if ($unread > 0): ?>
                                <form method="POST" action="/account/notifications/read-all" data-notif-readall>
                                    <?= csrf_field() ?>
                                    <button type="submit" class="notif-readall">Tandai semua dibaca</button>
                                </form>
                            <?php endif; ?>
                        </div>
                        <div class="notif-list">
                            <?php if (empty($notifs)): ?>
                                <div class="notif-empty">Belum ada notifikasi.</div>
                            <?php else: ?>
                                <?php foreach ($notifs as $n): ?>
                                <a href="<?= e($n['link'] ?? '/account') ?>" class="notif-item<?= $n['is_read'] ? '' : ' unread' ?>" data-read-url="<?= $n['is_read'] ? '' : '/account/notifications/' . (int)$n['id'] . '/read' ?>">
                                    <span class="notif-pip"></span>
                                    <span class="notif-body">
                                        <span class="notif-title"><?= e($n['title']) ?></span>
                                        <?php if ($n['message']): ?><span class="notif-msg"><?= e($n['message']) ?></span><?php endif; ?>
                                        <span class="notif-time"><?= time_ago($n['created_at']) ?></span>
                                    </span>
                                </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <div class="notif-foot">
                            <a href="/account/notifications" class="notif-seeall">Lihat Semua Notifikasi</a>
                        </div>
                    </div>
                </div>
                <div class="account" data-account>
                    <button class="account-trigger" type="button" aria-expanded="false" aria-haspopup="true">
                        <span class="account-avatar"><?= e(mb_strtoupper(mb_substr($_SESSION['user_name'], 0, 1))) ?></span>
                        <span class="account-name account-name-hide-mobile"><?= e($_SESSION['user_name']) ?></span>
                        <svg class="account-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                    </button>
                    <div class="account-menu" role="menu">
                        <div class="account-menu-head">
                            <strong><?= e($_SESSION['user_name']) ?></strong>
                            <span><?= e($_SESSION['user_email'] ?? '') ?></span>
                        </div>
                        <a href="/account" role="menuitem">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            Profil Saya
                        </a>
                        <a href="/orders" role="menuitem">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2z"/><path d="M8 8h8M8 12h8M8 16h5"/></svg>
                            Pesanan Saya
                        </a>
                        <?php if (Auth::admin()): ?>
                            <a href="/admin" role="menuitem">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l3 6 6 .5-4.5 4 1.3 6L12 16.7 6.2 19.5l1.3-6L3 9.5 9 9z"/></svg>
                                Admin Panel
                            </a>
                        <?php endif; ?>
                        <div class="account-menu-sep"></div>
                        <form method="POST" action="/auth/logout">
                            <?= csrf_field() ?>
                            <button type="submit" role="menuitem" class="danger overlay-link-btn">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (!Auth::check()): ?>
                <a href="/auth/login" class="btn btn-sm btn-ghost">Masuk</a>
                <a href="/auth/register" class="btn btn-sm btn-primary">Daftar</a>
            <?php endif; ?>
            <button class="nav-burger" id="navBurger" aria-label="Menu" aria-expanded="false">
                <span></span><span></span>
            </button>
        </div>
    </div>
</nav>

<div class="nav-overlay" id="navOverlay" aria-hidden="true">
    <div class="overlay-inner">
        <div class="overlay-top">
            <span class="overlay-brand">Geprek Geh</span>
            <button class="overlay-close" id="navClose" aria-label="Tutup menu" onclick="closeNav()">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <nav class="overlay-links" role="navigation">
            <?php $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); ?>
            <a class="overlay-link <?= $uri === '/products' || str_starts_with($uri, '/products/') ? 'is-active' : '' ?>" href="/products"><small>01</small> Menu</a>
            <a class="overlay-link cart-link" href="/cart"><small>02</small> Keranjang<span class="cart-count" data-cart-count="<?= CartController::count() ?>"><?= CartController::count() ?></span></a>
            <?php if (!Auth::check()): ?>
                <a class="overlay-link <?= $uri === '/auth/login' ? 'is-active' : '' ?>" href="/auth/login"><small>03</small> Masuk</a>
                <a class="overlay-link <?= $uri === '/auth/register' ? 'is-active' : '' ?>" href="/auth/register"><small>04</small> Daftar</a>
            <?php else: ?>
                <a class="overlay-link <?= $uri === '/account' ? 'is-active' : '' ?>" href="/account"><small>03</small> Profil Saya</a>
                <a class="overlay-link <?= $uri === '/wishlist' ? 'is-active' : '' ?>" href="/wishlist"><small class="ov-num">04</small> Daftar Keinginan<span class="cart-count" data-wish-count><?= count(wishlist_ids()) ?></span></a>
                <a class="overlay-link <?= $uri === '/orders' || str_starts_with($uri, '/orders/') ? 'is-active' : '' ?>" href="/orders"><small>05</small> Pesanan Saya</a>
                <?php if (Auth::admin()): ?>
                    <a class="overlay-link" href="/admin"><small class="ov-num">06</small> Admin Panel</a>
                <?php endif; ?>
            <?php endif; ?>
        </nav>
        <div class="overlay-cta">
            <a href="/products" class="btn btn-primary">Pesan Sekarang
                <span class="btn-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7M9 7h8v8"/></svg>
                </span>
            </a>
        </div>
        <div class="overlay-foot">
            <span>Geprek Pedas Nikmat</span>
            <span>Jakarta · Bandung · Online</span>
        </div>
    </div>
</div>

<main class="main">
    <div class="container">
        <?php $f = flash(); if ($f): ?>
            <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['msg']) ?></div>
        <?php endif; ?>