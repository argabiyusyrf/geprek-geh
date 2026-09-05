<?php $active_cat_slug = $_GET['category'] ?? ''; ?>
<section class="menu-hero">
    <div class="menu-hero-inner">
        <div class="menu-hero-content">
            <span class="menu-eyebrow">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3 6 6 .5-4.5 4 1.3 6L12 16.7 6.2 19.5l1.3-6L3 9.5 9 9z"/></svg>
                Menu Geprek Geh
            </span>
            <h1 class="menu-hero-title">Pilihan <em>Pedas</em><br>Untuk Lidahmu</h1>
            <p class="menu-hero-sub">Ayam geprek original, level super pedas, paket irit, hingga minuman dingin. Semua fresh, diantar hangat sampai pintu rumahmu.</p>
            <div class="menu-hero-stats">
                <span class="menu-stat"><b><?= $total ?></b> Menu</span>
                <span class="menu-stat-dot"></span>
                <span class="menu-stat"><b><?= count($categories) ?></b> Kategori</span>
                <?php $feat_count = array_sum(array_map(fn($p) => (int)$p['is_featured'], $products)); ?>
                <span class="menu-stat-dot"></span>
                <span class="menu-stat"><b><?= $feat_count ?></b> Populer</span>
            </div>
        </div>
        <div class="menu-hero-deco" aria-hidden="true">
            <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="0.8" stroke-linecap="round" stroke-linejoin="round" opacity="0.12"><path d="M15.5 11.5a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0z"/><path d="M11 7V3M13.5 4.5L8.5 6.5M15 7.5l-6 1.5"/><path d="M6 1l-1 3M9 2L7.5 4"/></svg>
        </div>
    </div>
</section>

<section class="menu-filters">
    <form method="GET" action="/geprek-geh/products" class="menu-filter-row">
        <div class="menu-search">
            <svg class="menu-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
            <input type="text" name="q" placeholder="Cari menu..." value="<?= e($_GET['q'] ?? '') ?>" class="menu-search-input" autocomplete="off">
            <?php if (!empty($_GET['q'])): ?>
                <a href="/geprek-geh/products?category=<?= e($_GET['category'] ?? '') ?>" class="menu-search-clear">&times;</a>
            <?php endif; ?>
        </div>

        <div class="menu-category-pills">
            <a href="/geprek-geh/products<?= !empty($_GET['q']) ? '?q='.e($_GET['q']) : '' ?>"
               class="menu-pill <?= empty($_GET['category']) ? 'active' : '' ?>">
                Semua
            </a>
            <?php foreach ($categories as $cat): ?>
                <a href="/geprek-geh/products?category=<?= e($cat['slug']) ?><?= !empty($_GET['q']) ? '&q='.e($_GET['q']) : '' ?>"
                   class="menu-pill <?= ($_GET['category'] ?? '') === $cat['slug'] ? 'active' : '' ?>">
                    <?= e($cat['name']) ?>
                    <span class="menu-pill-count"><?= $cat['product_count'] ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </form>
</section>

<div class="menu-results">
    <span class="menu-results-text">
        <?php if (!empty($_GET['q']) || !empty($_GET['category'])): ?>
            <?php
            $active_cat_name = '';
            foreach ($categories as $cat) {
                if ($cat['slug'] === $active_cat_slug) { $active_cat_name = $cat['name']; break; }
            }
            ?>
            <?= $total ?> hasil
            <?php if (!empty($_GET['q'])): ?> untuk "<strong><?= e($_GET['q']) ?></strong>"<?php endif; ?>
            <?php if ($active_cat_name): ?> dalam <strong><?= e($active_cat_name) ?></strong><?php endif; ?>
            — <a href="/geprek-geh/products" class="menu-results-reset">Reset</a>
        <?php else: ?>
            Menampilkan semua <?= $total ?> menu
        <?php endif; ?>
    </span>
</div>

<?php if (empty($products)): ?>
    <div class="menu-empty">
        <div class="menu-empty-icon">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3M8 11h6"/></svg>
        </div>
        <h3>Tidak ada menu ditemukan</h3>
        <p>Coba kata kategori lain, atau reset pencarianmu.</p>
        <a href="/geprek-geh/products" class="btn btn-primary">Lihat Semua Menu</a>
    </div>
<?php else: ?>
    <div class="menu-bento">
        <?php foreach ($products as $i => $p):
            $is_feat = $p['is_featured'];
            $is_first_feat = ($is_feat && ($i === 0 || !$products[$i-1]['is_featured']));
            $low_stock = ($p['stock'] > 0 && $p['stock'] <= 5);
            $out_stock = ($p['stock'] <= 0);
        ?>
            <div class="menu-card <?= $is_feat ? 'menu-card--feat' : '' ?>" data-reveal>
                <div class="menu-card-core">
                    <a class="menu-card-img" href="/geprek-geh/products/<?= e($p['slug']) ?>">
                        <?php if ($p['image']): ?>
                            <img src="/geprek-geh/assets/uploads/products/<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
                        <?php else: ?>
                            <div class="menu-card-placeholder">
                                <?= product_art($p['name'], $p['category_name'], '', 200) ?>
                            </div>
                        <?php endif; ?>

                        <div class="menu-card-badges">
                            <?php if ($is_feat): ?>
                                <span class="menu-badge menu-badge--feat">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3 6 6 .5-4.5 4 1.3 6L12 16.7 6.2 19.5l1.3-6L3 9.5 9 9z"/></svg>
                                    Populer
                                </span>
                            <?php endif; ?>
                            <?php if ($out_stock): ?>
                                <span class="menu-badge menu-badge--out">Habis</span>
                            <?php elseif ($low_stock): ?>
                                <span class="menu-badge menu-badge--low">Sisa <?= $p['stock'] ?></span>
                            <?php endif; ?>
                        </div>
                    </a>

                    <div class="menu-card-body">
                        <span class="menu-card-cat"><?= e($p['category_name']) ?></span>
                        <h3 class="menu-card-title">
                            <a href="/geprek-geh/products/<?= e($p['slug']) ?>"><?= e($p['name']) ?></a>
                        </h3>
                        <?php if ($p['description']): ?>
                            <p class="menu-card-desc"><?= e(mb_strimwidth($p['description'], 0, 90, '...')) ?></p>
                        <?php endif; ?>
                        <div class="menu-card-foot">
                            <div class="menu-card-price"><?= rupiah($p['price']) ?></div>
                            <?php if (!$out_stock): ?>
                                <form method="POST" action="/geprek-geh/cart/add" class="menu-card-cart">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="menu-add-btn" title="Tambah ke keranjang">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($total_pages > 1): ?>
    <nav class="menu-pagination" aria-label="Navigasi halaman">
        <?php
        $qp = [];
        if (!empty($_GET['category'])) $qp['category'] = $_GET['category'];
        if (!empty($_GET['q'])) $qp['q'] = $_GET['q'];
        ?>
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>&<?= http_build_query($qp) ?>" class="menu-page-btn">&laquo;</a>
        <?php endif; ?>

        <?php
        $start = max(1, $page - 2);
        $end   = min($total_pages, $page + 2);
        if ($start > 1): ?>
            <a href="?page=1&<?= http_build_query($qp) ?>" class="menu-page-btn">1</a>
            <?php if ($start > 2): ?><span class="menu-page-dots">&hellip;</span><?php endif; ?>
        <?php endif; ?>

        <?php for ($i = $start; $i <= $end; $i++): ?>
            <a href="?page=<?= $i ?>&<?= http_build_query($qp) ?>"
               class="menu-page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>

        <?php if ($end < $total_pages): ?>
            <?php if ($end < $total_pages - 1): ?><span class="menu-page-dots">&hellip;</span><?php endif; ?>
            <a href="?page=<?= $total_pages ?>&<?= http_build_query($qp) ?>" class="menu-page-btn"><?= $total_pages ?></a>
        <?php endif; ?>

        <?php if ($page < $total_pages): ?>
            <a href="?page=<?= $page + 1 ?>&<?= http_build_query($qp) ?>" class="menu-page-btn">&raquo;</a>
        <?php endif; ?>
    </nav>
    <?php endif; ?>
<?php endif; ?>
