<?php $admin_page_title = 'Kelola Kategori'; ?>

<div class="page-header page-header--wrap">
    <div>
        <h1>Kategori</h1>
        <p class="page-sub"><?= count($categories) ?> kategori &middot; <?= array_sum(array_column($categories, 'product_count')) ?> produk</p>
    </div>
    <button type="button" class="btn btn-primary" data-open-category-drawer>+ Tambah Kategori</button>
</div>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Produk</th>
                <th>Urutan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $c):
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
                    <span class="table-name"><?= e($c['name']) ?></span>
                    <span class="table-slug">/<?= e($c['slug']) ?></span>
                </td>
                <td class="stock-cell"><?= $c['product_count'] ?></td>
                <td class="stock-cell"><?= (int) ($c['sort_order'] ?? 0) ?></td>
                <td>
                    <div class="table-actions">
                        <button type="button" class="btn btn-sm btn-outline" data-edit-category='<?= $cJson ?>'>Edit</button>
                        <form method="POST" action="/geprek-geh/admin/categories/<?= $c['id'] ?>/delete" class="inline-form inline-form--compact" data-confirm="Hapus kategori ini?">
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
            <label>Nama Kategori <span class="req">*</span></label>
            <input type="text" name="name" class="input <?= !empty($formErrors['name']) ? 'is-invalid' : '' ?>" value="<?= e(\fval($formOld, $formOld ?? [], 'name')) ?>" placeholder="cth. Nasi Geprek" required>
            <?php if (!empty($formErrors['name'])): ?><span class="field-error"><?= e($formErrors['name']) ?></span><?php endif; ?>
        </div>

        <div class="form-group">
            <label>Deskripsi</label>
            <textarea name="description" class="input" rows="3" placeholder="Sekilas isi kategori"><?= e(\fval($formOld, $formOld ?? [], 'description')) ?></textarea>
        </div>

        <div class="form-group">
            <label>Urutan Tampil</label>
            <input type="number" name="sort_order" class="input" min="0" value="<?= e(\fval($formOld, $formOld ?? [], 'sort_order', 0)) ?>">
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