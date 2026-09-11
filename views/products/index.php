<?php
$active_cat_slug = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'populer';
$menu_qs_common = ['sort' => $sort];
if (!empty($_GET['q'])) $menu_qs_common['q'] = $_GET['q'];
$menu_qs = http_build_query($menu_qs_common);
$menu_href_all = '/geprek-geh/products' . ($menu_qs !== '' ? '?' . $menu_qs : '');
$open_time = '';
if (!empty($app['contacts']['hours']) && preg_match('/(\d{2}:\d{2})\s*[–-]\s*(\d{2}:\d{2})/', $app['contacts']['hours'], $mh)) {
    $open_time = $mh[1] . '–' . $mh[2];
}
?>
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
                <?php if ($open_time): ?>
                <span class="menu-stat-dot"></span>
                <span class="menu-stat"><b><?= e($open_time) ?></b> Jam Buka</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="menu-hero-deco" aria-hidden="true">
            <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="0.8" stroke-linecap="round" stroke-linejoin="round" opacity="0.12"><path d="M15.5 11.5a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0z"/><path d="M11 7V3M13.5 4.5L8.5 6.5M15 7.5l-6 1.5"/><path d="M6 1l-1 3M9 2L7.5 4"/></svg>
        </div>
    </div>
</section>

<section class="menu-filters">
    <div class="menu-toolbar">
        <form method="GET" action="/geprek-geh/products" class="menu-filter-row">
            <div class="menu-toolbar-top">
                <div class="menu-search">
                    <svg class="menu-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                    <input type="text" name="q" placeholder="Cari menu..." value="<?= e($_GET['q'] ?? '') ?>" class="menu-search-input" autocomplete="off">
                    <?php if (!empty($_GET['q'])): ?>
                        <a href="<?= e($menu_href_all) ?>" class="menu-search-clear" aria-label="Bersihkan pencarian">&times;</a>
                    <?php endif; ?>
                </div>

                <span class="menu-sort">
                    <span class="menu-sort-label">Urutkan</span>
                    <span class="menu-sort-box">
                        <select name="sort" class="menu-sort-select" onchange="this.form.submit()" aria-label="Urutkan menu">
                            <option value="populer"<?= $sort === 'populer' ? ' selected' : '' ?>>Terpopuler</option>
                            <option value="terbaru"<?= $sort === 'terbaru' ? ' selected' : '' ?>>Terbaru</option>
                            <option value="termurah"<?= $sort === 'termurah' ? ' selected' : '' ?>>Harga Terendah</option>
                            <option value="termahal"<?= $sort === 'termahal' ? ' selected' : '' ?>>Harga Tertinggi</option>
                        </select>
                        <svg class="menu-sort-chev" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                    </span>
                </span>
            </div>

            <div class="menu-cat-scroll">
                <div class="menu-category-pills">
                    <a href="<?= e($menu_href_all) ?>"
                       class="menu-pill <?= empty($_GET['category']) ? 'active' : '' ?>">
                        Semua
                    </a>
                    <?php foreach ($categories as $cat): ?>
                        <a href="/geprek-geh/products?category=<?= e($cat['slug']) ?>&<?= $menu_qs ?>"
                           class="menu-pill <?= ($_GET['category'] ?? '') === $cat['slug'] ? 'active' : '' ?>">
                            <?= e($cat['name']) ?>
                            <span class="menu-pill-count"><?= $cat['product_count'] ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </form>

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
    </div>
</section>

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
            <article class="product-card <?= $is_feat ? 'product-card--feat' : '' ?>" data-reveal>
                <div class="product-card-core">
                    <a class="product-img" href="/geprek-geh/products/<?= e($p['slug']) ?>">
                        <?php if ($p['image']): ?>
                            <img src="/geprek-geh/assets/uploads/products/<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
                        <?php else: ?>
                            <?= product_art($p['name'], $p['category_name'], '', 240) ?>
                        <?php endif; ?>

                        <span class="product-badges">
                            <?php if ($is_feat && $is_first_feat): ?>
                                <span class="badge badge--gold">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3 6 6 .5-4.5 4 1.3 6L12 16.7 6.2 19.5l1.3-6L3 9.5 9 9z"/></svg>
                                    Populer
                                </span>
                            <?php endif; ?>
                            <?php if ($out_stock): ?>
                                <span class="badge badge--red">Habis</span>
                            <?php elseif ($low_stock): ?>
                                <span class="badge badge--white">Sisa <?= $p['stock'] ?></span>
                            <?php endif; ?>
                        </span>
                    </a>

                    <div class="product-body">
                        <span class="product-cat"><?= e($p['category_name']) ?></span>
                        <h3 class="product-title">
                            <a href="/geprek-geh/products/<?= e($p['slug']) ?>"><?= e($p['name']) ?></a>
                        </h3>
                        <?php if ($p['review_count'] > 0): ?>
                        <div class="product-rating">
                            <?php for ($s = 1; $s <= 5; $s++): ?>
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="<?= $s <= round($p['avg_rating']) ? '#D43E1B' : 'none' ?>" stroke="#D43E1B" stroke-width="1.8"><path d="M12 2l3 6 6 .5-4.5 4 1.3 6L12 16.7 6.2 19.5l1.3-6L3 9.5 9 9z"/></svg>
                            <?php endfor; ?>
                            <span class="product-rating-count">(<?= $p['review_count'] ?>)</span>
                        </div>
                        <?php endif; ?>
                        <?php if ($p['description']): ?>
                            <p class="product-desc"><?= e(mb_strimwidth($p['description'], 0, 84, '…')) ?></p>
                        <?php endif; ?>
                        <div class="product-foot">
                            <b class="product-price"><?= rupiah($p['price']) ?></b>
                            <?php if (!$out_stock): ?>
                                <form method="POST" action="/geprek-geh/cart/add" class="product-add-form">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="product-add" title="Tambah ke keranjang">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if ($total_pages > 1): ?>
    <nav class="menu-pagination" aria-label="Navigasi halaman">
        <?php
        $qp = [];
        if (!empty($_GET['category'])) $qp['category'] = $_GET['category'];
        if (!empty($_GET['q'])) $qp['q'] = $_GET['q'];
        if ($sort !== 'populer') $qp['sort'] = $sort;
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
