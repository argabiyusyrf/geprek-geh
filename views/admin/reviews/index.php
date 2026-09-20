<?php
$status_label = ['visible' => 'Ditampilkan', 'hidden' => 'Disembunyikan', '' => 'Semua Status'][$status] ?? 'Semua Status';
?>

<div class="breadcrumb">
    <a href="/admin">Dashboard</a>
    <span>/</span>
    <span>Moderasi Ulasan</span>
</div>

<div class="page-header page-header--wrap">
    <div>
        <h1>Moderasi Ulasan</h1>
        <p class="page-sub">Tampilkan, sembunyikan, atau hapus ulasan pelanggan</p>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card stat-card--rev">
        <span class="stat-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12h4l3-8 4 16 3-8h4"/></svg>
        </span>
        <div class="stat-number"><?= (int) $kpis['total'] ?></div>
        <div class="stat-label">Total Ulasan</div>
    </div>
    <div class="stat-card stat-card--success">
        <span class="stat-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
        </span>
        <div class="stat-number"><?= (int) $kpis['visible'] ?></div>
        <div class="stat-label">Ditampilkan</div>
    </div>
    <div class="stat-card stat-card--pending">
        <span class="stat-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
        </span>
        <div class="stat-number"><?= (int) $kpis['hidden'] ?></div>
        <div class="stat-label">Disembunyikan</div>
    </div>
</div>

<div class="menu-filters admin-orders-filters">
    <form method="GET" action="/admin/reviews" class="menu-toolbar">
        <div class="menu-filter-row">
            <div class="menu-toolbar-top">
                <div class="menu-search">
                    <svg class="menu-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                    <input type="search" name="q" placeholder="Cari pelanggan, produk, atau isi…" value="<?= e($q) ?>" class="menu-search-input" autocomplete="off" aria-label="Cari ulasan">
                    <?php if ($q !== ''): ?>
                        <a href="/admin/reviews<?= $status !== '' ? '?status=' . e($status) : '' ?>" class="menu-search-clear" aria-label="Bersihkan pencarian">&times;</a>
                    <?php endif; ?>
                </div>
            </div>
            <span class="menu-sort">
                <span class="menu-sort-label">Status</span>
                <span class="menu-sort-box menu-dropdown" data-dropdown data-form-submit>
                    <button type="button" class="menu-dropdown-trigger" data-dropdown-trigger aria-haspopup="listbox" aria-expanded="false">
                        <span data-dropdown-label><?= e($status_label) ?></span>
                        <svg class="menu-sort-chev menu-sort-chev--js" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                    </button>
                    <div class="menu-dropdown-menu" data-dropdown-menu role="listbox">
                        <button type="button" class="menu-dropdown-item" data-value="" role="option">Semua Status</button>
                        <button type="button" class="menu-dropdown-item" data-value="visible" role="option">Ditampilkan</button>
                        <button type="button" class="menu-dropdown-item" data-value="hidden" role="option">Disembunyikan</button>
                    </div>
                    <select name="status" class="menu-sort-select menu-sort-native" data-dropdown-select onchange="this.form.submit()" aria-label="Filter status">
                        <option value=""<?= $status === '' ? ' selected' : '' ?>>Semua Status</option>
                        <option value="visible"<?= $status === 'visible' ? ' selected' : '' ?>>Ditampilkan</option>
                        <option value="hidden"<?= $status === 'hidden' ? ' selected' : '' ?>>Disembunyikan</option>
                    </select>
                    <svg class="menu-sort-chev menu-sort-chev--native" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                </span>
            </span>
        </div>
    </form>
</div>

<?php if (empty($reviews)): ?>
    <div class="card order-card">
        <div class="empty-state empty-state--compact">
            <span class="ghost"><svg style="width:4.5rem;height:4.5rem" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l2.9 6.3 6.9.5-5.2 4.6 1.7 6.8L12 16.8l-6.3 3.4 1.7-6.8L2.2 8.8l6.9-.5z"/></svg></span>
            <h3>Tidak ada ulasan</h3>
            <p>Belum ada ulasan yang cocok dengan filter.</p>
        </div>
    </div>
<?php else: ?>
<div class="review-mod-list">
    <?php foreach ($reviews as $r): ?>
        <div class="card order-card review-mod-card <?= $r['is_visible'] ? '' : 'review-mod-hidden' ?>">
            <div class="review-mod-head">
                <div class="review-mod-user">
                    <div class="review-mod-avatar"><?= e(mb_strtoupper(mb_substr($r['user_name'], 0, 1))) ?></div>
                    <div>
                        <div class="review-mod-name"><?= e($r['user_name']) ?>
                            <span class="review-mod-product">→ <?= e($r['product_name']) ?></span>
                        </div>
                        <div class="review-mod-meta">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <svg class="review-star <?= $i <= (int) $r['rating'] ? 'on' : '' ?>" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2l3 6 6 .5-4.5 4 1.3 6L12 16.7 6.2 19.5l1.3-6L3 9.5 9 9z"/></svg>
                            <?php endfor; ?>
                            <span class="badge<?= $r['is_visible'] ? ' badge-success' : ' badge-dark' ?>"><?= $r['is_visible'] ? 'Ditampilkan' : 'Disembunyikan' ?></span>
                        </div>
                    </div>
                </div>
                <div class="table-actions">
                    <form method="POST" action="/admin/reviews/<?= (int) $r['id'] ?>/toggle">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm <?= $r['is_visible'] ? 'btn-outline' : 'btn-primary' ?>"><?= $r['is_visible'] ? 'Sembunyikan' : 'Tampilkan' ?></button>
                    </form>
                    <form method="POST" action="/admin/reviews/<?= (int) $r['id'] ?>/delete" onsubmit="return confirm('Hapus ulasan ini? Tindakan tidak dapat dibatalkan.');">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-danger-ghost">Hapus</button>
                    </form>
                </div>
            </div>
            <?php if ($r['comment'] !== null && $r['comment'] !== ''): ?>
                <p class="review-mod-comment"><?= e($r['comment']) ?></p>
            <?php endif; ?>
            <?php if (!empty($r['image'])): ?>
                <a href="/assets/uploads/reviews/<?= e($r['image']) ?>" target="_blank" rel="noopener" class="review-mod-photo">
                    <img src="/assets/uploads/reviews/<?= e($r['image']) ?>" alt="Foto ulasan" loading="lazy">
                </a>
            <?php endif; ?>
            <p class="review-mod-date"><?= e(date('d M Y H:i', strtotime($r['created_at']))) ?></p>
        </div>
    <?php endforeach; ?>
</div>

<nav class="menu-pagination menu-pagination--orders" aria-label="Navigasi halaman">
    <?php if ($total_pages > 1): ?>
        <div class="menu-pagination-pages review-pagination">
            <?php if ($page > 1): ?>
                <a href="/admin/reviews?page=<?= $page - 1 ?><?= $status !== '' ? '&status=' . e($status) : '' ?><?= $q !== '' ? '&q=' . e(urlencode($q)) : '' ?>" class="menu-page-btn">&laquo;</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="/admin/reviews?page=<?= $i ?><?= $status !== '' ? '&status=' . e($status) : '' ?><?= $q !== '' ? '&q=' . e(urlencode($q)) : '' ?>" class="menu-page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($page < $total_pages): ?>
                <a href="/admin/reviews?page=<?= $page + 1 ?><?= $status !== '' ? '&status=' . e($status) : '' ?><?= $q !== '' ? '&q=' . e(urlencode($q)) : '' ?>" class="menu-page-btn">&raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</nav>
<?php endif; ?>