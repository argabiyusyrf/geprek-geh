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
    <button type="button" class="btn btn-primary" data-open-product-drawer>+ Tambah Produk</button>
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
                    'description' => $p['description'] ?? '',
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
                            <button class="btn btn-sm btn-danger">Hapus</button>
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
        <h4 id="product-drawer-title">Tambah Produk</h4>
        <button type="button" class="drawer-close" data-close-product-drawer aria-label="Tutup">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
        </button>
    </div>

    <form method="POST" action="/geprek-geh/admin/products" enctype="multipart/form-data" class="drawer-body" id="product-form">
        <?= csrf_field() ?>

        <div class="form-group">
            <label>Nama Produk <span class="req">*</span></label>
            <input type="text" name="name" id="product-name" class="input" placeholder="cth. Ayam Geprek Original" required>
            <span class="product-slug-preview" id="product-slug-preview">/nama-produk</span>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Kategori <span class="req">*</span></label>
                <select name="category_id" class="input" required>
                    <option value="">Pilih</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Harga (Rp)</label>
                <input type="number" name="price" class="input" required min="0" placeholder="15000">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Stok</label>
                <input type="number" name="stock" class="input" required min="0" value="0">
            </div>
            <div class="form-group">
                <label>Gambar</label>
                <input type="file" name="image" id="product-image-input" class="input" accept="image/png,image/jpeg,image/webp">
            </div>
        </div>

        <div id="product-image-preview-wrap" class="product-img-preview-wrap" hidden>
            <img id="product-image-preview" class="product-img-preview" alt="Pratinjau gambar produk">
            <span class="product-img-note" id="product-image-note"></span>
        </div>

        <div class="form-group">
            <label>Deskripsi</label>
            <textarea name="description" class="input" rows="4" placeholder="Cara penyajian, level, bahan, dll."></textarea>
        </div>

        <div class="form-row">
            <label class="checkbox-label"><input type="checkbox" name="is_active" checked> Aktif</label>
            <label class="checkbox-label"><input type="checkbox" name="is_featured"> ★ Favorit</label>
        </div>
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
        'description' => $p['description'] ?? '',
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
</script>