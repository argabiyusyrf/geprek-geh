<?php $admin_page_title = 'Tambah Produk'; ?>

<div class="page-header"><h1>Tambah Produk</h1></div>

<div class="card">
    <form method="POST" action="/geprek-geh/admin/products" enctype="multipart/form-data">
        <div class="form-row">
            <div class="form-group" style="flex:2">
                <label>Nama Produk</label>
                <input type="text" name="name" class="input" required>
            </div>
            <div class="form-group" style="flex:1">
                <label>Kategori</label>
                <select name="category_id" class="input" required>
                    <option value="">Pilih</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Harga (Rp)</label>
                <input type="number" name="price" class="input" required min="0">
            </div>
            <div class="form-group">
                <label>Stok</label>
                <input type="number" name="stock" class="input" required min="0" value="0">
            </div>
            <div class="form-group">
                <label>Gambar</label>
                <input type="file" name="image" class="input" accept="image/*">
            </div>
        </div>
        <div class="form-group">
            <label>Deskripsi</label>
            <textarea name="description" class="input" rows="4"></textarea>
        </div>
        <div class="form-row">
            <label class="checkbox-label"><input type="checkbox" name="is_active" checked> Aktif</label>
            <label class="checkbox-label"><input type="checkbox" name="is_featured"> Favorit</label>
        </div>
        <button type="submit" class="btn btn-primary">💾 Simpan</button>
        <a href="/geprek-geh/admin/products" class="btn btn-outline">Batal</a>
    </form>
</div>
