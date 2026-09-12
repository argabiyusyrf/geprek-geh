<?php $admin_page_title = 'Kelola Kategori'; ?>

<div class="page-header page-header--wrap">
    <div>
        <h1>Kategori</h1>
        <p class="page-sub"><?= $cstats['total'] ?> kategori &middot; <?= $cstats['products'] ?> produk tersebar</p>
    </div>
    <button type="button" class="btn btn-primary" data-open-category-drawer>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
        Tambah Kategori
    </button>
</div>

<div class="stats-grid stats-grid--cats">
    <div class="stat-card stat-card--cat">
        <span class="stat-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0L3 13V3h10l7.6 7.6a2 2 0 0 1 0 2.8z"/><path d="M8 8h.01"/></svg>
        </span>
        <div class="stat-number"><?= $cstats['total'] ?></div>
        <div class="stat-label">Total Kategori</div>
    </div>
    <div class="stat-card stat-card--prod">
        <span class="stat-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8 12 3 3 8v8l9 5 9-5z"/><path d="M3 8l9 5 9-5M12 13v8"/></svg>
        </span>
        <div class="stat-number"><?= $cstats['products'] ?></div>
        <div class="stat-label">Produk Terkategori</div>
    </div>
    <div class="stat-card stat-card--order">
        <span class="stat-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m7 15 3-4 3 2 5-6"/></svg>
        </span>
        <div class="stat-number"><?= rtrim(rtrim(number_format($cstats['avg'], 1), '0'), '.') ?></div>
        <div class="stat-label">Rata-rata Produk / Kategori</div>
    </div>
    <div class="stat-card stat-card--pending">
        <span class="stat-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
        </span>
        <div class="stat-number"><?= $cstats['empty'] ?></div>
        <div class="stat-label">Kategori Kosong</div>
    </div>
</div>

<?php if ($categories): ?>
<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Kategori</th>
                <th>Deskripsi</th>
                <th>Produk</th>
                <th>Urutan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $catPalette = [
                ['#a82a0e', '#d43e1b'],
                ['#0d9488', '#14b8a6'],
                ['#7a5ab8', '#9f7aea'],
                ['#b45309', '#d97706'],
                ['#1d7a8c', '#2ba6bd'],
                ['#3f6212', '#65a30d'],
            ];
            foreach ($categories as $i => $c):
                $cCount = (int) ($c['product_count'] ?? 0);
                [$cC1, $cC2] = $catPalette[$i % count($catPalette)];
                $cInitial = mb_strtoupper(mb_substr(trim($c['name']), 0, 1, 'UTF-8'));
                $cJson = e(json_encode([
                    'id'          => (int) $c['id'],
                    'name'        => $c['name'],
                    'slug'        => $c['slug'],
                    'description' => $c['description'] ?? '',
                    'sort_order'  => (int) ($c['sort_order'] ?? 0),
                ], JSON_UNESCAPED_UNICODE));
            ?>
            <tr>
                <td>
                    <div class="cat-cell">
                        <span class="cat-swatch" style="--c1: <?= $cC1 ?>; --c2: <?= $cC2 ?>"><?= e($cInitial) ?></span>
                        <div>
                            <span class="table-name"><?= e($c['name']) ?></span>
                            <span class="table-slug">/<?= e($c['slug']) ?></span>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="cat-desc"><?= $c['description'] !== '' && $c['description'] !== null ? nl2br(e($c['description'])) : '<em>Tidak ada deskripsi.</em>' ?></span>
                </td>
                <td><span class="count-pill <?= $cCount === 0 ? 'count-pill--zero' : '' ?>"><?= $cCount ?></span></td>
                <td class="stock-cell cat-sort"><?= (int) ($c['sort_order'] ?? 0) ?></td>
                <td>
                    <div class="table-actions">
                        <button type="button" class="btn btn-sm btn-outline" data-edit-category='<?= $cJson ?>'>Edit</button>
                        <form method="POST" action="/geprek-geh/admin/categories/<?= $c['id'] ?>/delete" class="inline-form inline-form--compact" data-confirm="Hapus kategori ini? Produk di dalamnya berpindah ke &ldquo;Tanpa Kategori&rdquo;.">
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
<?php else: ?>
<div class="table-wrap">
    <div class="empty-state">
        <span class="ghost">🗂️</span>
        <h3>Belum ada kategori</h3>
        <p>Buat kategori pertama untuk mengelompokkan menu Anda.</p>
        <button type="button" class="btn btn-primary" data-open-category-drawer>+ Tambah Kategori</button>
    </div>
</div>
<?php endif; ?>

<div class="drawer-overlay" id="category-drawer-overlay" data-drawer-overlay></div>
<aside class="drawer" id="category-drawer" data-category-drawer aria-hidden="true" data-lenis-prevent>
    <div class="drawer-head">
        <div>
            <span class="drawer-eyebrow" id="category-drawer-eyebrow">Kategori</span>
            <h4 id="category-drawer-title">Tambah Kategori</h4>
        </div>
        <button type="button" class="drawer-close" data-close-category-drawer aria-label="Tutup">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
        </button>
    </div>

    <form method="POST" action="/geprek-geh/admin/categories" class="drawer-body" id="category-form">
        <?= csrf_field() ?>

        <div class="form-group">
            <label class="f-label"><span class="f-ico"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0L3 13V3h10l7.6 7.6a2 2 0 0 1 0 2.8z"/><circle cx="8" cy="8" r="1"/></svg></span> Nama Kategori <span class="req">*</span></label>
            <input type="text" name="name" id="category-name" class="input <?= !empty($formErrors['name']) ? 'is-invalid' : '' ?>" value="<?= e(\fval($formOld, [], 'name')) ?>" placeholder="cth. Nasi Geprek" required>
            <span class="product-slug-preview" id="category-slug-preview">/nama-kategori</span>
            <?php if (!empty($formErrors['name'])): ?><span class="field-error"><?= e($formErrors['name']) ?></span><?php endif; ?>
        </div>

        <div class="form-group">
            <label class="f-label"><span class="f-ico"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7V5a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v2M4 7v12a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V7M9 7V5M15 7V5M4 12h16M4 17h16"/></svg></span> Deskripsi</label>
            <textarea name="description" class="input" rows="3" placeholder="Sekilas isi kategori"><?= e(\fval($formOld, [], 'description')) ?></textarea>
        </div>

        <div class="form-group">
            <label class="f-label"><span class="f-ico"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 6h7M5 10h4M5 14h9M5 18h6"/><path d="m17 9 2-2 2 2M19 7v10"/></svg></span> Urutan Tampil</label>
            <input type="number" name="sort_order" class="input" min="0" value="<?= e(\fval($formOld, [], 'sort_order', 0)) ?>">
            <span class="field-hint">Semakin kecil angka, semakin dulu muncul di menu.</span>
        </div>
    </form>

    <div class="drawer-foot">
        <button type="button" class="btn btn-ghost" data-close-category-drawer>Batal</button>
        <button type="submit" form="category-form" class="btn btn-primary" id="category-submit">Tambah Kategori</button>
    </div>
</aside>

<?php
$_jsonOpts = JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG;
$__categoriesData = array_map(function ($c) {
    return [
        'id'          => (int) $c['id'],
        'name'        => $c['name'],
        'slug'        => $c['slug'],
        'description' => $c['description'] ?? '',
        'sort_order'  => (int) ($c['sort_order'] ?? 0),
    ];
}, $categories);
$_drawerError = $formOld !== null && $formErrors !== null;
?>
<script>
window.__gehCategories = <?= json_encode($__categoriesData, $_jsonOpts) ?>;
window.__gehDrawerError = <?= $_drawerError ? (isset($_GET['edit']) ? json_encode(['mode' => 'edit', 'id' => (int) $_GET['edit']], $_jsonOpts) : json_encode(['mode' => 'create', 'id' => null], $_jsonOpts)) : 'null' ?>;
</script>