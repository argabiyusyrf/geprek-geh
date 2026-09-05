<?php $page_title = $product['name']; ?>
<?php $out_stock = $product['stock'] <= 0; ?>

<div class="pd-wrap">
    <nav class="pd-crumb" aria-label="Breadcrumb">
        <a href="/geprek-geh/">Beranda</a><i>/</i>
        <a href="/geprek-geh/products">Menu</a><i>/</i>
        <a href="/geprek-geh/products?category=<?= e($product['category_slug']) ?>"><?= e($product['category_name']) ?></a><i>/</i>
        <span><?= e($product['name']) ?></span>
    </nav>

    <div class="pd">
        <!-- Visual -->
        <div class="pd-visual" data-pd-visual>
            <?php if ($product['image']): ?>
                <img class="pd-photo" src="/geprek-geh/assets/uploads/products/<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>">
            <?php else: ?>
                <?= product_art($product['name'], $product['category_name'], 'pd-art', 900) ?>
            <?php endif; ?>

            <?php if ($product['is_featured']): ?>
                <span class="pd-chip pd-chip--feat">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3 6 6 .5-4.5 4 1.3 6L12 16.7 6.2 19.5l1.3-6L3 9.5 9 9z"/></svg>
                    Menu Populer
                </span>
            <?php endif; ?>

            <div class="pd-float pd-float--stock <?= $out_stock ? 'is-out' : '' ?>">
                <span class="pd-dot"></span>
                <?= $out_stock ? 'Habis hari ini' : ($product['stock'] <= 5 ? "Sisa {$product['stock']} porsi" : 'Siap diantar') ?>
            </div>

            <div class="pd-caption">
                <span><?= e($product['category_name']) ?></span>
                <span><?= $product['is_featured'] ? 'Signature Menu' : 'Menu Geprek Geh' ?></span>
            </div>
        </div>

        <!-- Info -->
        <div class="pd-info">
            <span class="eyebrow"><?= e($product['category_name']) ?></span>
            <h1 class="pd-title"><?= e($product['name']) ?></h1>

            <?php if ($product['description']): ?>
                <p class="pd-desc"><?= nl2br(e($product['description'])) ?></p>
            <?php endif; ?>

            <div class="pd-legenda">
                <div class="pd-legenda-item">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                    <span><b><?= $product['stock'] ?></b> porsi tersedia</span>
                </div>
                <div class="pd-legenda-item">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l8 4v6c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6z"/><path d="M9 14l2 2 4-4"/></svg>
                    <span>Sambal level bisa dikustom</span>
                </div>
                <div class="pd-legenda-item">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2v6a6 6 0 0 0 12 0V2M6 2h4M18 2h-4"/><path d="M6 2v6L4 6M18 2v6l2-2"/></svg>
                    <span>Diantar hangat ±30 menit</span>
                </div>
            </div>

            <div class="pd-buy">
                <div class="pd-price">
                    <span class="pd-price-label">Harga per porsi</span>
                    <strong><?= rupiah($product['price']) ?></strong>
                </div>

                <?php if ($product['stock'] > 0): ?>
                <form method="POST" action="/geprek-geh/cart/add" class="pd-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <div class="pd-qty">
                        <button type="button" class="pd-qty-btn" aria-label="Kurangi" onclick="changeQty(-1)">&minus;</button>
                        <input type="number" name="quantity" id="qty" value="1" min="1" max="<?= $product['stock'] ?>" class="pd-qty-input">
                        <button type="button" class="pd-qty-btn" aria-label="Tambah" onclick="changeQty(1)">+</button>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg pd-add">
                        Tambah ke Keranjang
                        <span class="btn-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1.5"/><circle cx="19" cy="21" r="1.5"/><path d="M2.5 3h2l2.6 12.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L22.5 7H6"/></svg>
                        </span>
                    </button>
                </form>
                <?php else: ?>
                    <button type="button" class="btn btn-lg pd-add is-soldout" disabled>Stok Habis</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($related)): ?>
<section class="section">
    <div class="section-head">
        <div>
            <span class="eyebrow" data-reveal>Lengkapi</span>
            <h2 class="section-title" data-reveal>Menu serupa<br>&amp; favorit</h2>
        </div>
        <a href="/geprek-geh/products" class="section-link" data-reveal>Semua menu
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7M9 7h8v8"/></svg>
        </a>
    </div>
    <div class="product-grid">
        <?php foreach ($related as $p): ?>
            <article class="product-card" data-reveal>
                <div class="product-card-inner">
                    <a class="product-img" href="/geprek-geh/products/<?= e($p['slug']) ?>">
                        <?php if ($p['image']): ?>
                            <img src="/geprek-geh/assets/uploads/products/<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
                        <?php else: ?>
                            <?= product_art($p['name'], $p['category_name'], '', 300) ?>
                        <?php endif; ?>
                        <?php if ($p['stock'] <= 0): ?><span class="stock-badge">Habis</span><?php endif; ?>
                    </a>
                    <div class="product-info">
                        <span class="product-cat"><?= e($p['category_name']) ?></span>
                        <h3><a href="/geprek-geh/products/<?= e($p['slug']) ?>"><?= e($p['name']) ?></a></h3>
                        <div class="product-price"><?= rupiah($p['price']) ?></div>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>