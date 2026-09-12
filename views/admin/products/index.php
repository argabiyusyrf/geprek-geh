<?php $admin_page_title = 'Kelola Produk'; ?>

<div class="page-header page-header--wrap">
    <div>
        <h1>Produk</h1>
        <p class="page-sub">
            <?= count($products) ?> produk &middot; <?= count($categories) ?> kategori
            <?php if ($lowStockCount > 0): ?>
                &middot; <span class="stock-low"><?= $lowStockCount ?> stok menipis</span>
            <?php endif; ?>
        </p>
    </div>
    <button type="button" class="btn btn-primary" data-open-product-drawer>
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
        Tambah Produk
    </button>
</div>

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
                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if (empty($products)): ?>
    <div class="admin-empty">
        <p>Belum ada produk. Klik <b>“+ Tambah Produk”</b> untuk mulai.</p>
    </div>
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
                <textarea name="description" hidden data-editor-input></textarea>
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
                <span class="switch-meta"><strong>★ Favorit</strong><small>Tampil di blok “Menu populer” beranda</small></span>
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