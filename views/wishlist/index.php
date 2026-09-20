<?php $admin_page_title = 'Daftar Keinginan'; ?>

<div class="page-header page-header--wrap">
    <div>
        <h1>Daftar Keinginan</h1>
        <p class="page-sub">Produk favoritmu yang tersimpan — kapan pun siap, tinggal masukkan ke keranjang.</p>
    </div>
    <div class="page-header-actions">
        <a href="/geprek-geh/products" class="btn btn-ghost">Lihat Semua Menu</a>
    </div>
</div>

<?php if (empty($products)): ?>
    <div class="empty-state">
        <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21.2l7.8-7.8 1-1a5.5 5.5 0 0 0 0-7.8z"/></svg>
        <h3>Belum ada produk tersimpan</h3>
        <p>Klik ikon <span class="heart-pill">♥</span> pada produk yang kamu suka agar tersimpan di sini.</p>
        <a href="/geprek-geh/products" class="btn btn-primary">Jelajahi Menu</a>
    </div>
<?php else: ?>
    <div class="menu-bento">
        <?php foreach ($products as $p):
            $out_stock = ((int) $p['stock']) <= 0;
        ?>
        <article class="product-card" data-reveal>
            <div class="product-card-core">
                <a class="product-img" href="/geprek-geh/products/<?= e($p['slug']) ?>">
                    <?php if ($p['image']): ?>
                        <img src="/geprek-geh/assets/uploads/products/<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
                    <?php else: ?>
                        <?= product_art($p['name'], $p['category_name'] ?? '', '', 240) ?>
                    <?php endif; ?>
                    <?php if ($out_stock): ?><span class="badge badge--red">Habis</span><?php endif; ?>
                </a>
                <div class="product-body">
                    <span class="product-cat"><?= e($p['category_name'] ?? '') ?></span>
                    <h3 class="product-title">
                        <a href="/geprek-geh/products/<?= e($p['slug']) ?>"><?= e($p['name']) ?></a>
                    </h3>
                    <?php if ((int) $p['review_count'] > 0): ?>
                    <div class="product-rating">
                        <?php for ($s = 1; $s <= 5; $s++): ?>
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="<?= $s <= round((float) $p['avg_rating']) ? '#D43E1B' : 'none' ?>" stroke="#D43E1B" stroke-width="1.8"><path d="M12 2l3 6 6 .5-4.5 4 1.3 6L12 16.7 6.2 19.5l1.3-6L3 9.5 9 9z"/></svg>
                        <?php endfor; ?>
                        <span class="product-rating-count">(<?= (int) $p['review_count'] ?>)</span>
                    </div>
                    <?php endif; ?>
                    <div class="product-foot">
                        <b class="product-price"><?= rupiah($p['price']) ?></b>
                        <?php if (!$out_stock): ?>
                            <form method="POST" action="/geprek-geh/cart/add" class="product-add-form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="product-add" title="Tambah ke keranjang">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                                </button>
                            </form>
                        <?php endif; ?>
                        <form method="POST" action="/geprek-geh/wishlist/<?= (int) $p['id'] ?>/toggle" class="wish-form">
                            <?= csrf_field() ?>
                            <button type="submit" class="product-wish" title="Hapus dari daftar keinginan">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="#D43E1B" stroke="#D43E1B" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21.2l7.8-7.8 1-1a5.5 5.5 0 0 0 0-7.8z"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>