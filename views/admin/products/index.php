<?php $admin_page_title = 'Kelola Produk'; ?>

<div class="page-header page-header--wrap">
    <div>
        <h1>Produk</h1>
        <p class="page-sub">
            <?= $total ?> produk &middot; <?= count($categories) ?> kategori
            <?php if ($lowStockCount > 0): ?>
                &middot; <span class="stock-low"><?= $lowStockCount ?> stok menipis</span>
            <?php endif; ?>
            <?php if ($q !== ''): ?>
                &middot; hasil untuk <b>“<?= e($q) ?>”</b>
            <?php endif; ?>
        </p>
    </div>
    <button type="button" class="btn btn-primary" data-open-product-drawer>
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
        Tambah Produk
    </button>
</div>

<div class="admin-grid-2 chart-grid">
    <article class="card chart-card">
        <div class="chart-inner">
            <div class="chart-head">
                <div>
                    <h3>Produk Terlaris</h3>
                    <p class="chart-head-sub">Peringkat unit terjual dari semua pesanan</p>
                </div>
                <?php if ($top_total > 0): ?><span class="chart-meta"><?= $top_total ?> unit</span><?php endif; ?>
            </div>
            <?php if (empty($top_sellers)): ?>
                <div class="chart-empty"><p>Belum ada penjualan tercatat. Chart ini otomatis terisi saat pesanan mulai masuk.</p></div>
            <?php else: ?>
            <div class="sellers">
                <div class="sellers-colhead">
                    <span class="sch-gap"></span>
                    <span class="sch-menu">Menu</span>
                    <span class="sch-qty">Terjual</span>
                </div>
                <?php foreach ($top_sellers as $i => $t):
                    $pct   = $top_max > 0 ? round($t['qty'] / $top_max * 100) : 0;
                    $share = $top_total > 0 ? round($t['qty'] / $top_total * 100) : 0;
                    $rank  = $i + 1;
                ?>
                <div class="seller">
                    <span class="seller-rank r<?= min(3, $rank) ?>"><?= str_pad((string) $rank, 2, '0', STR_PAD_LEFT) ?></span>
                    <?php if ($t['image']): ?>
                        <img class="seller-thumb" src="/geprek-geh/assets/uploads/products/<?= e($t['image']) ?>" loading="lazy" alt="">
                    <?php else: ?>
                        <?= product_art($t['name'], $t['category_name'], 'seller-thumb seller-art') ?>
                    <?php endif; ?>
                    <span class="seller-main">
                        <strong class="seller-name" title="<?= e($t['name']) ?>"><?= e($t['name']) ?></strong>
                        <span class="seller-cat"><?= $t['category_name'] !== '' ? e($t['category_name']) : 'Tanpa kategori' ?></span>
                        <span class="seller-track"><span class="seller-fill" style="--w:<?= $pct ?>%;--i:<?= $i ?>"></span></span>
                    </span>
                    <span class="seller-num">
                        <b><?= $t['qty'] ?> unit</b>
                        <small><?= $share ?>% dari penjualan</small>
                    </span>
                </div>
                <?php endforeach; ?>
                <div class="sellers-foot">
                    <span>Total unit terjual</span>
                    <strong><?= $top_total ?> unit</strong>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </article>

    <article class="card chart-card">
        <div class="chart-inner">
            <div class="chart-head">
                <div>
                    <h3>Perlu Perhatian</h3>
                    <p class="chart-head-sub">Stok menipis atau habis, segera restok</p>
                </div>
                <?php if ($lowStockCount > 0): ?><span class="chart-meta chart-meta--warn"><?= $lowStockCount ?> item</span><?php endif; ?>
            </div>
            <div class="watchlist">
                <?php if (empty($low_stock_items)): ?>
                    <div class="chart-empty"><p>Semua stok dalam kondisi sehat. Tidak ada produk yang perlu ditindaklanjuti.</p></div>
                <?php else: foreach ($low_stock_items as $ls): $_ls_out = (int) $ls['stock'] === 0; ?>
                <div class="watch-item">
                    <?php if ($ls['image']): ?>
                        <img class="watch-item-art" src="/geprek-geh/assets/uploads/products/<?= e($ls['image']) ?>" loading="lazy" alt="">
                    <?php else: ?>
                        <?= product_art($ls['name'], $ls['category_name'], 'watch-item-art watch-art') ?>
                    <?php endif; ?>
                    <span class="watch-item-main">
                        <strong class="watch-item-name" title="<?= e($ls['name']) ?>"><?= e($ls['name']) ?></strong>
                        <span class="watch-item-cat"><?= e($ls['category_name']) ?></span>
                    </span>
                    <span class="watch-item-num">
                        <span class="badge <?= $_ls_out ? 'badge-danger' : 'badge-warning' ?>"><?= $_ls_out ? 'Habis' : 'Sisa ' . (int) $ls['stock'] ?></span>
                    </span>
                </div>
                <?php endforeach; ?>
                <a class="watch-item-more" href="/geprek-geh/admin/products?sort=stock_low">
                    Kelola stok produk
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7M9 7h8v8"/></svg>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </article>
</div>

<?php
$sortOptions = [
    'newest'     => 'Terbaru',
    'oldest'     => 'Terlama',
    'name_asc'   => 'Nama A–Z',
    'name_desc'  => 'Nama Z–A',
    'price_asc'  => 'Harga Terendah',
    'price_desc' => 'Harga Tertinggi',
    'stock_low'  => 'Stok Menipis',
    'stock_high' => 'Stok Terbanyak',
];
$sortLabel = $sortOptions[$sort] ?? 'Terbaru';
$keep = [];
foreach (['q', 'sort'] as $k) {
    if (isset($filter[$k]) && $filter[$k] !== '') $keep[$k] = $filter[$k];
}
$catHref = function ($cid) use ($keep, $status) {
    $qs = $keep;
    if ($cid > 0) $qs['category'] = $cid;
    if ($status !== '') $qs['status'] = $status;
    return '/geprek-geh/admin/products' . ($qs ? '?' . http_build_query($qs) : '');
};
$statusHref = function ($st) use ($keep, $category) {
    $qs = $keep;
    if ($category > 0) $qs['category'] = $category;
    if ($st !== '') $qs['status'] = $st;
    return '/geprek-geh/admin/products' . ($qs ? '?' . http_build_query($qs) : '');
};
?>
<section class="menu-filters" aria-label="Filter produk">
    <div class="menu-toolbar">
        <form method="GET" action="/geprek-geh/admin/products" class="menu-filter-row">
            <div class="menu-toolbar-top">
                <div class="menu-search">
                    <svg class="menu-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                    <input type="text" name="q" placeholder="Cari produk atau slug…" value="<?= e($q) ?>" class="menu-search-input" autocomplete="off" aria-label="Cari produk">
                    <?php if ($q !== ''): ?><a href="/geprek-geh/admin/products" class="menu-search-clear" aria-label="Bersihkan pencarian">&times;</a><?php endif; ?>
                </div>
                <span class="menu-sort">
                    <span class="menu-sort-label">Urutkan</span>
                    <span class="menu-sort-box menu-dropdown" data-dropdown data-form-submit>
                        <button type="button" class="menu-dropdown-trigger" data-dropdown-trigger aria-haspopup="listbox" aria-expanded="false">
                            <span data-dropdown-label><?= e($sortLabel) ?></span>
                            <svg class="menu-sort-chev menu-sort-chev--js" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                        </button>
                        <div class="menu-dropdown-menu" data-dropdown-menu role="listbox">
                            <?php foreach ($sortOptions as $sv => $sl): ?>
                            <button type="button" class="menu-dropdown-item" data-value="<?= e($sv) ?>" role="option"><?= e($sl) ?></button>
                            <?php endforeach; ?>
                        </div>
                        <select name="sort" class="menu-sort-select menu-sort-native" data-dropdown-select onchange="this.form.submit()" aria-label="Urutkan produk">
                            <?php foreach ($sortOptions as $sv => $sl): ?>
                            <option value="<?= e($sv) ?>"<?= $sort === $sv ? ' selected' : '' ?>><?= e($sl) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <svg class="menu-sort-chev menu-sort-chev--native" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                    </span>
                </span>
            </div>

            <div class="menu-cat-scroll">
                <div class="menu-category-pills" aria-label="Filter kategori">
                    <a href="<?= e($catHref(0)) ?>" class="menu-pill <?= $category === 0 ? 'active' : '' ?>">Semua</a>
                    <?php foreach ($categories as $c): ?>
                        <a href="<?= e($catHref((int) $c['id'])) ?>" class="menu-pill <?= $category === (int) $c['id'] ? 'active' : '' ?>">
                            <?= e($c['name']) ?> <span class="menu-pill-count"><?= (int) ($c['product_count'] ?? 0) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="filter-tabs">
                <a href="<?= e($statusHref('')) ?>" class="btn btn-sm <?= $status === '' ? 'btn-primary' : 'btn-outline' ?>">Semua <span class="order-filter-count"><?= $total ?></span></a>
                <a href="<?= e($statusHref('active')) ?>" class="btn btn-sm <?= $status === 'active' ? 'btn-success' : 'btn-outline' ?>">Aktif <span class="order-filter-count"><?= $status_counts['active'] ?></span></a>
                <a href="<?= e($statusHref('inactive')) ?>" class="btn btn-sm <?= $status === 'inactive' ? 'btn-danger' : 'btn-outline' ?>">Nonaktif <span class="order-filter-count"><?= $status_counts['inactive'] ?></span></a>
                <a href="<?= e($statusHref('featured')) ?>" class="btn btn-sm <?= $status === 'featured' ? 'btn-warning' : 'btn-outline' ?>">Favorit <span class="order-filter-count"><?= $status_counts['featured'] ?></span></a>
            </div>
        </form>

        <div class="menu-results">
            <span class="menu-results-text">
                <?php if ($total > 0): ?>
                    Menampilkan <strong><?= $offset + 1 ?>–<?= min($total, $offset + $per_page) ?></strong> dari <strong><?= $total ?></strong> produk
                <?php else: ?>
                    Tidak ada produk yang cocok dengan filter
                <?php endif; ?>
                <?php if ($filter_active): ?> — <a href="/geprek-geh/admin/products" class="menu-results-reset">Reset filter</a><?php endif; ?>
            </span>
        </div>
    </div>
</section>

<?php if ($total > 0): ?>
<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Gambar</th>
                <th>Nama</th>
                <th>Kategori</th>
                <th>Harga</th>
                <th>Stok</th>
                <th>Favorit</th>
                <th>Status</th>
                <th>Update</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $p):
                $pJson = e(json_encode([
                    'id'          => (int) $p['id'],
                    'name'        => $p['name'],
                    'slug'        => $p['slug'],
                    'category_id' => (int) $p['category_id'],
                    'price'       => (int) $p['price'],
                    'stock'       => (int) $p['stock'],
                    'description' => \sanitize_rich_text($p['description'] ?? ''),
                    'image'       => $p['image'] ?? '',
                    'is_active'   => (int) $p['is_active'],
                    'is_featured' => (int) $p['is_featured'],
                ], JSON_UNESCAPED_UNICODE));
            ?>
            <tr>
                <td>
                    <?php if ($p['image']): ?>
                        <img src="/geprek-geh/assets/uploads/products/<?= e($p['image']) ?>" class="table-thumb" loading="lazy">
                    <?php else: ?>
                        <div class="table-thumb-placeholder"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><path d="M15.5 11.5a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0z"/><path d="M11 7V3M13.5 4.5L8.5 6.5M15 7.5l-6 1.5"/><path d="M6 1l-1 3M9 2L7.5 4"/></svg></div>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="table-name"><?= e($p['name']) ?></span>
                    <span class="table-slug">/<?= e($p['slug']) ?></span>
                </td>
                <td><span class="badge badge-secondary"><?= e($p['category_name']) ?></span></td>
                <td class="stock-cell"><?= rupiah($p['price']) ?></td>
                <td class="stock-cell <?= $p['is_active'] && (int) $p['stock'] <= 5 ? 'stock-low' : '' ?>">
                    <?= (int) $p['stock'] ?>
                    <?php if ($p['is_active'] && (int) $p['stock'] <= 5): ?>
                        <span class="badge badge-danger"><?= (int) $p['stock'] === 0 ? 'Habis' : 'Menipis' ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($p['is_featured']): ?>
                        <span class="badge badge-warning">★ Favorit</span>
                    <?php else: ?>
                        <span class="table-slug">—</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($p['is_active']): ?>
                        <span class="badge badge-success">Aktif</span>
                    <?php else: ?>
                        <span class="badge badge-danger">Nonaktif</span>
                    <?php endif; ?>
                </td>
                <td class="stock-cell"><?= time_ago($p['updated_at']) ?></td>
                <td>
                    <div class="table-actions">
                        <button type="button" class="btn btn-sm btn-outline" data-edit-product='<?= $pJson ?>'>Edit</button>
                        <form method="POST" action="/geprek-geh/admin/products/<?= $p['id'] ?>/delete" class="inline-form inline-form--compact" data-confirm="Hapus produk ini? Tindakan ini tidak bisa dibatalkan.">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($total_pages > 1): ?>
<nav class="menu-pagination" aria-label="Navigasi halaman produk">
    <?php if ($page > 1): ?>
        <a class="menu-page-btn" href="/geprek-geh/admin/products?<?= e(http_build_query(array_merge($filter, ['page' => $page - 1]))) ?>" aria-label="Halaman sebelumnya">&laquo;</a>
    <?php endif; ?>
    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a class="menu-page-btn <?= $i === $page ? 'active' : '' ?>" href="/geprek-geh/admin/products?<?= e(http_build_query(array_merge($filter, ['page' => $i]))) ?>"><?= $i ?></a>
    <?php endfor; ?>
    <?php if ($page < $total_pages): ?>
        <a class="menu-page-btn" href="/geprek-geh/admin/products?<?= e(http_build_query(array_merge($filter, ['page' => $page + 1]))) ?>" aria-label="Halaman berikutnya">&raquo;</a>
    <?php endif; ?>
</nav>
<?php endif; ?>
<?php else: ?>
    <?php if ($filter_active): ?>
        <div class="admin-empty">
            <p>Tidak ada produk yang cocok dengan pencarian atau filter.</p>
            <a href="/geprek-geh/admin/products" class="btn btn-outline btn-sm">Reset filter</a>
        </div>
    <?php else: ?>
        <div class="admin-empty">
            <p>Belum ada produk. Klik <b>&ldquo;+ Tambah Produk&rdquo;</b> untuk mulai.</p>
        </div>
    <?php endif; ?>
<?php endif; ?>

<div class="drawer-overlay" id="product-drawer-overlay" data-drawer-overlay></div>
<aside class="drawer" id="product-drawer" data-product-drawer aria-hidden="true" data-lenis-prevent>
    <div class="drawer-head">
        <div>
            <span class="drawer-eyebrow" id="product-drawer-eyebrow">Produk</span>
            <h4 id="product-drawer-title">Tambah Produk</h4>
        </div>
        <button type="button" class="drawer-close" data-close-product-drawer aria-label="Tutup">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
        </button>
    </div>

    <form method="POST" action="/geprek-geh/admin/products" enctype="multipart/form-data" class="drawer-body" id="product-form">
        <?= csrf_field() ?>

        <div class="form-group">
            <label class="f-label"><span class="f-ico"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0L3 13V3h10z"/><circle cx="7.5" cy="7.5" r="1.5"/></svg></span> Nama Produk <span class="req">*</span></label>
            <div class="input-ico">
                <span class="input-ico-sym"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.1 4.1L20 7.2l-2.4 4.8L20 16.8l-4.9 1.1L12 22l-3.1-4.1L4 16.8l2.4-4.8L4 7.2l4.9-1.1z"/></svg></span>
                <input type="text" name="name" id="product-name" class="input <?= !empty($formErrors['name']) ? 'is-invalid' : '' ?>" value="<?= e(\fval($formOld, [], 'name')) ?>" placeholder="cth. Ayam Geprek Original" required>
            </div>
            <?php if (!empty($formErrors['name'])): ?><span class="field-error"><?= e($formErrors['name']) ?></span><?php endif; ?>
            <span class="product-slug-preview" id="product-slug-preview">/nama-produk</span>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="f-label"><span class="f-ico"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 2 7l10 5 10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg></span> Kategori <span class="req">*</span></label>
                <span class="select-wrap">
                    <select name="category_id" class="input <?= !empty($formErrors['category_id']) ? 'is-invalid' : '' ?>" required>
                        <option value="">Pilih</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= (int) (\fval($formOld, [], 'category_id', 0)) === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <svg class="select-chev" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                </span>
                <?php if (!empty($formErrors['category_id'])): ?><span class="field-error"><?= e($formErrors['category_id']) ?></span><?php endif; ?>
            </div>
            <div class="form-group">
                <label class="f-label"><span class="f-ico"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v10M9.5 9.5h5M9.5 14h5"/></svg></span> Harga <span class="req">*</span></label>
                <span class="pricer">
                    <span class="pricer-prefix">Rp</span>
                    <input type="text" inputmode="numeric" name="price" autocomplete="off" class="input pricer-input <?= !empty($formErrors['price']) ? 'is-invalid' : '' ?>" required placeholder="15.000" value="<?= e(number_format((int) \fval($formOld, [], 'price', 0), 0, ',', '.')) ?>">
                </span>
                <?php if (!empty($formErrors['price'])): ?><span class="field-error"><?= e($formErrors['price']) ?></span><?php endif; ?>
            </div>
        </div>

        <div class="form-group">
            <label class="f-label"><span class="f-ico"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="M3.3 7 12 12l8.7-5M12 22V12"/></svg></span> Stok <span class="req">*</span></label>
            <div class="stock-ctrl">
                <input type="range" min="0" max="500" step="1" class="stock-range" data-stock-range aria-label="Stok produk" value="0">
                <div class="stock-mini">
                    <button type="button" class="stock-mini-btn" data-stock-step="-1" aria-label="Kurangi stok">&minus;</button>
                    <input type="number" name="stock" class="input stock-mini-num" min="0" value="<?= e((int) \fval($formOld, [], 'stock', 0)) ?>" aria-label="Jumlah stok">
                    <button type="button" class="stock-mini-btn" data-stock-step="1" aria-label="Tambah stok">+</button>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="f-label"><span class="f-ico"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg></span> Gambar</label>
            <label class="pimg-field" data-pimg-field>
                <input type="file" name="image" accept="image/png,image/jpeg,image/webp" class="pimg-input" data-pimg-input>
                <span class="pimg-empty" data-pimg-empty>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>
                    <span class="pimg-empty-title">Pilih gambar produk</span>
                    <span class="pimg-empty-hint">PNG, JPG, WebP &middot; maks. 5MB &middot; rasio 4:3 disarankan</span>
                </span>
                <span class="pimg-preview" data-pimg-preview hidden>
                    <span class="pimg-preview-main">
                        <img class="pimg-preview-img" data-pimg-img alt="Pratinjau gambar produk">
                        <span class="pimg-preview-side">
                            <span class="pimg-preview-name" data-pimg-name></span>
                            <span class="pimg-preview-note" data-pimg-note></span>
                        </span>
                    </span>
                    <span class="pimg-actions" data-pimg-actions>
                        <span class="pimg-btn" data-pimg-replace tabindex="0">Ganti</span>
                        <span class="pimg-btn pimg-btn-danger" data-pimg-clear tabindex="0" hidden>Batal</span>
                    </span>
                </span>
            </label>
            <p class="pimg-error" data-pimg-error hidden></p>
        </div>

        <div class="form-group">
            <label class="f-label"><span class="f-ico"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7V5a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v2M4 7v12a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V7M9 7V5M15 7V5M4 12h16M4 17h16"/></svg></span> Deskripsi</label>
            <div class="editor" data-editor-root>
                <div class="editor-toolbar" role="toolbar" aria-label="Pemformatan teks">
                    <button type="button" class="editor-btn" data-ed="bold" title="Tebal (B)" aria-label="Tebal"><b>B</b></button>
                    <button type="button" class="editor-btn" data-ed="italic" title="Miring (I)" aria-label="Miring"><i>I</i></button>
                    <button type="button" class="editor-btn" data-ed="underline" title="Garis bawah (U)" aria-label="Garis bawah"><u>U</u></button>
                    <span class="editor-hint">Pilih teks lalu format: <b>B</b>, <i>I</i>, <u>U</u></span>
                </div>
                <div class="editor-area" contenteditable="true" data-editor role="textbox" aria-multiline="true" aria-label="Deskripsi produk" data-placeholder="Cara penyajian, level sambal, bahan, dll."></div>
                <textarea name="description" hidden data-editor-input><?= e(\fval($formOld, [], 'description', '')) ?></textarea>
            </div>
        </div>

        <?php $activeDefault = $formOld !== null ? isset($formOld['is_active']) : true; ?>
        <?php $featDefault  = $formOld !== null ? isset($formOld['is_featured']) : false; ?>
        <fieldset class="fieldset-switches">
            <label class="switch">
                <input type="checkbox" name="is_active" class="switch-input" <?= $activeDefault ? 'checked' : '' ?>>
                <span class="switch-ui" aria-hidden="true"><span class="switch-knob"></span></span>
                <span class="switch-meta"><strong>Aktif</strong><small>Muncul di katalog &amp; menu publik</small></span>
            </label>
            <label class="switch">
                <input type="checkbox" name="is_featured" class="switch-input" <?= $featDefault ? 'checked' : '' ?>>
                <span class="switch-ui" aria-hidden="true"><span class="switch-knob"></span></span>
                <span class="switch-meta"><strong><span class="sw-star">★</span> Favorit</strong><small>Tampil di blok “Menu populer” beranda</small></span>
            </label>
        </fieldset>
    </form>

    <div class="drawer-foot">
        <button type="button" class="btn btn-ghost" data-close-product-drawer>Batal</button>
        <button type="submit" form="product-form" class="btn btn-primary" id="product-submit">Tambah Produk</button>
    </div>
</aside>

<?php
$_jsonOpts = JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG;
$__productsData = array_map(function ($p) {
    return [
        'id'          => (int) $p['id'],
        'name'        => $p['name'],
        'slug'        => $p['slug'],
        'category_id' => (int) $p['category_id'],
        'price'       => (int) $p['price'],
        'stock'       => (int) $p['stock'],
        'description' => \sanitize_rich_text($p['description'] ?? ''),
        'image'       => $p['image'] ?? '',
        'is_active'   => (int) $p['is_active'],
        'is_featured' => (int) $p['is_featured'],
    ];
}, $products);
$__categoriesData = array_map(function ($c) {
    return ['id' => (int) $c['id'], 'name' => $c['name']];
}, $categories);
?>
<script>
window.__gehProducts = <?= json_encode($__productsData, $_jsonOpts) ?>;
window.__gehCategories = <?= json_encode($__categoriesData, $_jsonOpts) ?>;
<?php
$_drawerError = $formOld !== null && $formErrors !== null;
if ($_drawerError) {
    $__errState = isset($_GET['edit'])
        ? json_encode(['mode' => 'edit', 'id' => (int) $_GET['edit']], $_jsonOpts)
        : json_encode(['mode' => 'create', 'id' => null], $_jsonOpts);
    echo 'window.__gehDrawerError = ' . $__errState . ';' . "\n";
} else {
    echo 'window.__gehDrawerError = null;' . "\n";
}
?>
</script>