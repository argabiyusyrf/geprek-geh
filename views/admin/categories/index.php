<?php $admin_page_title = 'Kelola Kategori'; ?>

<div class="page-header"><h1>Kategori</h1></div>

<div class="admin-grid-2">
    <div class="card">
        <h3>Tambah Kategori</h3>
        <form method="POST" action="/geprek-geh/admin/categories">
            <div class="form-group">
                <label>Nama</label>
                <input type="text" name="name" class="input" required>
            </div>
            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="description" class="input" rows="2"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">➕ Tambah</button>
        </form>
    </div>

    <div class="card">
        <h3>Daftar Kategori</h3>
        <?php if (empty($categories)): ?>
            <p class="text-muted">Belum ada kategori.</p>
        <?php else: ?>
        <table class="table">
            <thead><tr><th>Nama</th><th>Produk</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($categories as $c): ?>
                <tr>
                    <td><?= e($c['name']) ?></td>
                    <td><?= $c['product_count'] ?></td>
                    <td>
                        <form method="POST" action="/geprek-geh/admin/categories/<?= $c['id'] ?>/delete" class="inline-form" style="display:inline" data-confirm="Hapus kategori ini? Produk di dalamnya juga akan dihapus.">
                            <button class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
