<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#14110C">
    <meta name="description" content="<?= e($page_description ?? 'Geprek Geh — Ayam geprek renyah, sambal level sesuai seleramu, diantar hangat.') ?>">
    <title><?= e($page_title ?? 'Geprek Geh') ?> — Geprek Geh</title>

    <meta property="og:site_name" content="Geprek Geh">
    <meta property="og:title" content="<?= e(($page_title ?? 'Geprek Geh') . ' — Geprek Geh') ?>">
    <meta property="og:description" content="<?= e($page_description ?? 'Ayam geprek renyah, sambal level sesuai seleramu, diantar hangat.') ?>">
    <meta property="og:type" content="<?= e($og_type ?? 'website') ?>">
    <meta property="og:url" content="<?= e('http' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 's' : '') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '/geprek-geh/')) ?>">
    <?php if (!empty($og_image)): ?>
        <meta property="og:image" content="<?= e($og_image) ?>">
    <?php endif; ?>

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e(($page_title ?? 'Geprek Geh') . ' — Geprek Geh') ?>">
    <meta name="twitter:description" content="<?= e($page_description ?? 'Ayam geprek renyah, sambal level sesuai seleramu, diantar hangat.') ?>">
    <?php if (!empty($og_image)): ?>
        <meta name="twitter:image" content="<?= e($og_image) ?>">
    <?php endif; ?>

    <link rel="canonical" href="<?= e('http' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 's' : '') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '/geprek-geh/')) ?>">
    <link rel="icon" type="image/svg+xml" href="/geprek-geh/public/favicon.svg">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="/geprek-geh/public/fonts/fonts.css">
    <link rel="stylesheet" href="/geprek-geh/vendor/css/lenis.css">
    <link rel="stylesheet" href="/geprek-geh/public/css/style.css?v=20260912v">

    <?= SeoController::organizationJsonLd() ?>
    <?= $page_jsonld ?? '' ?>
</head>
<body class="auth-body">

<?php $f = flash(); if ($f): ?>
    <div class="container" style="position:fixed;top:18px;left:50%;transform:translateX(-50%);z-index:9999;width:calc(100% - 36px);max-width:480px;">
        <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['msg']) ?></div>
    </div>
<?php endif; ?>
