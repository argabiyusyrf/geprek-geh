<?php $admin_page_title = 'Kelola Produk'; ?>

<div class="page-header">
    <h1>Produk</h1>
    <a href="/geprek-geh/admin/products/create" class="btn btn-primary">+ Tambah Produk</a>
</div>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Gambar</th>
                <th>Nama</th>
                <th>Kategori</th>
                <th>Harga</th>
                <th>Stok</th>
                <th>Aktif</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $p): ?>
            <tr>
                <td><?= $p['id'] ?></td>
                <td>
                    <?php if ($p['image']): ?>
                        <img src="/geprek-geh/assets/uploads/products/<?= e($p['image']) ?>" class="table-thumb">
                    <?php else: ?>
                        <div class="table-thumb-placeholder"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><path d="M15.5 11.5a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0z"/><path d="M11 7V3M13.5 4.5L8.5 6.5M15 7.5l-6 1.5"/><path d="M6 1l-1 3M9 2L7.5 4"/></svg></div>
                    <?php endif; ?>
                </td>
                <td><?= e($p['name']) ?></td>
                <td><?= e($p['category_name']) ?></td>
                <td><?= rupiah($p['price']) ?></td>
                <td><?= $p['stock'] ?></td>
                <td>
                    <?php if ($p['is_active']): ?>
                        <span class="badge badge-success">Aktif</span>
                    <?php else: ?>
                        <span class="badge badge-danger">Nonaktif</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="/geprek-geh/admin/products/<?= $p['id'] ?>/edit" class="btn btn-sm btn-outline">Edit</a>
                    <form method="POST" action="/geprek-geh/admin/products/<?= $p['id'] ?>/delete" class="inline-form" style="display:inline" data-confirm="Hapus produk ini? Tindakan ini tidak bisa dibatalkan.">
                        <button class="btn btn-sm btn-danger">Hapus</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
