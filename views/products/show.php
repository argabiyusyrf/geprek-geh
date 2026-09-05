<?php $page_title = $product['name']; ?>

<div class="page-top">
    <div class="breadcrumb">
        <a href="/geprek-geh/">Beranda</a> /
        <a href="/geprek-geh/products">Menu</a> /
        <a href="/geprek-geh/products?category=<?= e($product['category_slug']) ?>"><?= e($product['category_name']) ?></a> /
        <span><?= e($product['name']) ?></span>
    </div>

    <header class="page-hero">
        <h1><?= e($product['name']) ?></h1>
    </header>
</div>

<div class="product-detail">
    <div class="product-detail-img">
        <?php if ($product['image']): ?>
            <img src="/geprek-geh/assets/uploads/products/<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>">
        <?php else: ?>
            <span class="product-img-placeholder large"><svg width="140" height="140" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M15.5 11.5a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0z"/><path d="M11 7V3M13.5 4.5L8.5 6.5M15 7.5l-6 1.5"/><path d="M6 1l-1 3M9 2L7.5 4"/></svg></span>
        <?php endif; ?>
    </div>
    <div class="product-detail-info">
        <div class="product-price large"><?= rupiah($product['price']) ?></div>
        <div class="product-stock <?= $product['stock'] > 0 ? 'in-stock' : 'out-stock' ?>">
            <?= $product['stock'] > 0 ? "Stok: {$product['stock']}" : 'Habis' ?>
        </div>
        <?php if ($product['description']): ?>
            <div class="product-desc"><?= nl2br(e($product['description'])) ?></div>
        <?php endif; ?>
        <?php if ($product['stock'] > 0): ?>
        <form method="POST" action="/geprek-geh/cart/add" class="product-detail-form">
            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
            <label>Jumlah:</label>
            <div class="qty-group">
                <button type="button" class="btn btn-outline btn-sm" onclick="changeQty(-1)">−</button>
                <input type="number" name="quantity" id="qty" value="1" min="1" max="<?= $product['stock'] ?>" class="input qty-input">
                <button type="button" class="btn btn-outline btn-sm" onclick="changeQty(1)">+</button>
            </div>
            <button type="submit" class="btn btn-primary btn-lg">Tambah ke Keranjang
                <span class="btn-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1.5"/><circle cx="19" cy="21" r="1.5"/><path d="M2.5 3h2l2.6 12.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L22.5 7H6"/></svg>
                </span>
            </button>
        </form>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($related)): ?>
<section class="section">
    <h2 class="section-title">Menu Terkait</h2>
    <div class="product-grid">
        <?php foreach ($related as $p): ?>
            <div class="product-card" data-reveal>
                <div class="product-card-inner">
                    <a class="product-img" href="/geprek-geh/products/<?= e($p['slug']) ?>">
                        <?php if ($p['image']): ?>
                            <img src="/geprek-geh/assets/uploads/products/<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
                        <?php else: ?>
                            <span class="product-img-placeholder"><svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M15.5 11.5a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0z"/><path d="M11 7V3M13.5 4.5L8.5 6.5M15 7.5l-6 1.5"/><path d="M6 1l-1 3M9 2L7.5 4"/></svg></span>
                        <?php endif; ?>
                    </a>
                    <div class="product-info">
                        <span class="product-cat"><?= e($p['category_name']) ?></span>
                        <h3><a href="/geprek-geh/products/<?= e($p['slug']) ?>"><?= e($p['name']) ?></a></h3>
                        <div class="product-price"><?= rupiah($p['price']) ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>