<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title ?? 'Geprek Geh') ?> — Geprek Geh</title>
    <link rel="icon" type="image/svg+xml" href="/geprek-geh/public/favicon.svg">
    <link rel="stylesheet" href="/geprek-geh/public/fonts/fonts.css">
    <link rel="stylesheet" href="/geprek-geh/vendor/css/lenis.css">
    <link rel="stylesheet" href="/geprek-geh/public/css/style.css?v=20260905">
</head>
<body>

<nav class="island-nav">
    <div class="nav-pill">
        <a href="/geprek-geh/" class="brand">
            <span class="brand-mark">G</span>
            <span class="brand-word">Geprek Geh</span>
        </a>

        <div class="nav-inline">
            <a href="/geprek-geh/products">Menu</a>
            <button type="button" class="cart-link cart-trigger" data-open-drawer aria-label="Buka keranjang">
                Keranjang
                <span class="cart-count" data-cart-count="<?= CartController::count() ?>"><?= CartController::count() ?></span>
            </button>
            <?php if (Auth::check()): ?>
                <!-- account items live in the dropdown (nav-actions) -->
            <?php else: ?>
                <a href="/geprek-geh/auth/login" class="btn btn-sm btn-ghost">Masuk</a>
                <a href="/geprek-geh/auth/register" class="btn btn-sm btn-primary">Daftar</a>
            <?php endif; ?>
        </div>

        <div class="nav-actions">
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
                                <form method="POST" action="/geprek-geh/account/notifications/read-all" data-notif-readall>
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
                                <a href="<?= e($n['link'] ?? '/geprek-geh/account') ?>" class="notif-item<?= $n['is_read'] ? '' : ' unread' ?>" data-read-url="<?= $n['is_read'] ? '' : '/geprek-geh/account/notifications/' . (int)$n['id'] . '/read' ?>">
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
                        <a href="/geprek-geh/account" role="menuitem">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            Profil Saya
                        </a>
                        <a href="/geprek-geh/orders" role="menuitem">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2z"/><path d="M8 8h8M8 12h8M8 16h5"/></svg>
                            Pesanan Saya
                        </a>
                        <?php if (Auth::admin()): ?>
                            <a href="/geprek-geh/admin" role="menuitem">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l3 6 6 .5-4.5 4 1.3 6L12 16.7 6.2 19.5l1.3-6L3 9.5 9 9z"/></svg>
                                Admin Panel
                            </a>
                        <?php endif; ?>
                        <div class="account-menu-sep"></div>
                        <a href="/geprek-geh/auth/logout" role="menuitem" class="danger">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                            Keluar
                        </a>
                    </div>
                </div>
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
            <a class="overlay-link" href="/geprek-geh/products"><small>01</small> Menu</a>
            <a class="overlay-link cart-link" href="/geprek-geh/cart" data-open-drawer><small>02</small> Keranjang<span class="cart-count" data-cart-count="<?= CartController::count() ?>"><?= CartController::count() ?></span></a>
            <?php if (!Auth::check()): ?>
                <a class="overlay-link" href="/geprek-geh/auth/login"><small>03</small> Masuk</a>
                <a class="overlay-link" href="/geprek-geh/auth/register"><small>04</small> Daftar</a>
            <?php endif; ?>
        </nav>
        <div class="overlay-cta">
            <a href="/geprek-geh/products" class="btn btn-primary">Pesan Sekarang
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