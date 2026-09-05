<?php $admin_page_title = 'Edit Produk'; ?>

<div class="page-header"><h1>Edit: <?= e($product['name']) ?></h1></div>

<div class="card">
    <form method="POST" action="/geprek-geh/admin/products/<?= $product['id'] ?>" enctype="multipart/form-data">
        <div class="form-row">
            <div class="form-group" style="flex:2">
                <label>Nama Produk</label>
                <input type="text" name="name" class="input" value="<?= e($product['name']) ?>" required>
            </div>
            <div class="form-group" style="flex:1">
                <label>Kategori</label>
                <select name="category_id" class="input" required>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $c['id'] == $product['category_id'] ? 'selected' : '' ?>>
                            <?= e($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Harga (Rp)</label>
                <input type="number" name="price" class="input" value="<?= $product['price'] ?>" required min="0">
            </div>
            <div class="form-group">
                <label>Stok</label>
                <input type="number" name="stock" class="input" value="<?= $product['stock'] ?>" required min="0">
            </div>
            <div class="form-group">
                <label>Gambar Baru (opsional)</label>
                <input type="file" name="image" class="input" accept="image/*">
                <?php if ($product['image']): ?>
                    <small>当前: <?= e($product['image']) ?></small>
                <?php endif; ?>
            </div>
        </div>
        <div class="form-group">
            <label>Deskripsi</label>
            <textarea name="description" class="input" rows="4"><?= e($product['description']) ?></textarea>
        </div>
        <div class="form-row">
            <label class="checkbox-label"><input type="checkbox" name="is_active" <?= $product['is_active'] ? 'checked' : '' ?>> Aktif</label>
            <label class="checkbox-label"><input type="checkbox" name="is_featured" <?= $product['is_featured'] ? 'checked' : '' ?>> Favorit</label>
        </div>
        <button type="submit" class="btn btn-primary">💾 Update</button>
        <a href="/geprek-geh/admin/products" class="btn btn-outline">Batal</a>
    </form>
</div>
