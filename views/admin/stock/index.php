<?php
$sort_label = ['stok' => 'Stok Terendah', 'nama' => 'A–Z', 'kategori' => 'Kategori'][$sort] ?? 'Stok Terendah';
$q = $q ?? '';
?>

<div class="breadcrumb">
    <a href="/geprek-geh/admin">Dashboard</a>
    <span>/</span>
    <span>Manajemen Stok</span>
</div>

<div class="page-header page-header--wrap">
    <div>
        <h1>Manajemen Stok</h1>
        <p class="page-sub">Pantau stok, tandai produk menipis/kehabisan, dan tambah stok</p>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card stat-card--prod">
        <span class="stat-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l8 4v6c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6z"/></svg>
        </span>
        <div class="stat-number"><?= (int) $kpis['total'] ?></div>
        <div class="stat-label">Produk Aktif</div>
    </div>
    <div class="stat-card stat-card--pending">
        <span class="stat-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4M12 17h.01"/><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg>
        </span>
        <div class="stat-number"><?= (int) $kpis['low'] ?></div>
        <div class="stat-label">Stok Menipis</div>
    </div>
    <div class="stat-card stat-card--cust">
        <span class="stat-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M5 5l14 14"/></svg>
        </span>
        <div class="stat-number"><?= (int) $kpis['out'] ?></div>
        <div class="stat-label">Stok Habis</div>
    </div>
    <div class="stat-card stat-card--rev">
        <span class="stat-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 15l4-6 4 3 4-8"/></svg>
        </span>
        <div class="stat-number"><?= rupiah((int) $kpis['total_stock']) ?></div>
        <div class="stat-label">Total Stok (unit)</div>
    </div>
</div>

<div class="menu-filters admin-orders-filters">
    <form method="GET" action="/geprek-geh/admin/stock" class="menu-toolbar">
        <div class="menu-filter-row">
            <div class="menu-toolbar-top">
                <div class="menu-search">
                    <svg class="menu-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                    <input type="search" name="q" placeholder="Cari produk…" value="<?= e($q) ?>" class="menu-search-input" autocomplete="off" aria-label="Cari produk">
                    <?php if ($q !== ''): ?>
                        <a href="/geprek-geh/admin/stock" class="menu-search-clear" aria-label="Bersihkan pencarian">&times;</a>
                    <?php endif; ?>
                </div>
                <span class="menu-sort">
                    <span class="menu-sort-label">Urutkan</span>
                    <span class="menu-sort-box menu-dropdown" data-dropdown data-form-submit>
                        <button type="button" class="menu-dropdown-trigger" data-dropdown-trigger aria-haspopup="listbox" aria-expanded="false">
                            <span data-dropdown-label><?= e($sort_label) ?></span>
                            <svg class="menu-sort-chev menu-sort-chev--js" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                        </button>
                        <div class="menu-dropdown-menu" data-dropdown-menu role="listbox">
                            <button type="button" class="menu-dropdown-item" data-value="stok" role="option">Stok Terendah</button>
                            <button type="button" class="menu-dropdown-item" data-value="nama" role="option">A–Z</button>
                            <button type="button" class="menu-dropdown-item" data-value="kategori" role="option">Kategori</button>
                        </div>
                        <select name="sort" class="menu-sort-select menu-sort-native" data-dropdown-select onchange="this.form.submit()" aria-label="Urutkan stok">
                            <option value="stok"<?= $sort === 'stok' ? ' selected' : '' ?>>Stok Terendah</option>
                            <option value="nama"<?= $sort === 'nama' ? ' selected' : '' ?>>A–Z</option>
                            <option value="kategori"<?= $sort === 'kategori' ? ' selected' : '' ?>>Kategori</option>
                        </select>
                        <svg class="menu-sort-chev menu-sort-chev--native" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                    </span>
                </span>
            </div>
            <div class="menu-results">
                <span class="menu-results-text">Menampilkan <?= $total ?> produk</span>
            </div>
        </div>
    </form>
</div>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Produk</th>
                <th>Kategori</th>
                <th>Stok</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($products)): ?>
                <tr>
                    <td colspan="5">
                        <div class="empty-state empty-state--compact">
                            <span class="ghost"><svg style="width:4.5rem;height:4.5rem" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg></span>
                            <h3>Tidak ada produk</h3>
                            <p>Tidak ada produk yang cocok dengan pencarian.</p>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
            <?php foreach ($products as $p):
                $low = (int) $p['stock'] <= 0 ? 'out' : ((int) $p['stock'] <= $threshold ? 'low' : 'ok');
            ?>
                <tr>
                    <td class="ocell-invoice">
                        <span class="ocell-no"><?= e($p['name']) ?></span>
                        <span class="ocell-sub"><?= e($p['slug']) ?></span>
                    </td>
                    <td class="stock-cell"><?= e($p['category_name']) ?></td>
                    <td>
                        <span class="stock-pill <?= $low ?>"><?= (int) $p['stock'] ?></span>
                    </td>
                    <td>
                        <?php if ($low === 'out'): ?>
                            <span class="badge badge-danger">Stok Habis</span>
                        <?php elseif ($low === 'low'): ?>
                            <span class="badge badge-warning">Menipis</span>
                        <?php else: ?>
                            <span class="badge badge-success">Aman</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="table-actions">
                            <a href="/geprek-geh/admin/stock/<?= $p['id'] ?>" class="btn btn-sm btn-outline">Detail &amp; Tambah Stok</a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($total > 0): ?>
<nav class="menu-pagination menu-pagination--orders" aria-label="Navigasi halaman">
    <?php if ($total_pages > 1): ?>
        <div class="menu-pagination-pages">
            <?php if ($page > 1): ?>
                <a href="/geprek-geh/admin/stock?page=<?= $page - 1 ?>&sort=<?= e($sort) ?><?= $q !== '' ? '&q=' . e(urlencode($q)) : '' ?>" class="menu-page-btn">&laquo;</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="/geprek-geh/admin/stock?page=<?= $i ?>&sort=<?= e($sort) ?><?= $q !== '' ? '&q=' . e(urlencode($q)) : '' ?>" class="menu-page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($page < $total_pages): ?>
                <a href="/geprek-geh/admin/stock?page=<?= $page + 1 ?>&sort=<?= e($sort) ?><?= $q !== '' ? '&q=' . e(urlencode($q)) : '' ?>" class="menu-page-btn">&raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</nav>
<?php endif; ?>