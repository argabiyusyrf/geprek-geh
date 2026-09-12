<?php
$admin_page_title = 'Kelola Pesanan';
$filter_status = $status;
$status_tabs = [
    ''           => ['Semua', ''],
    'pending'    => ['Menunggu', 'warn'],
    'processing' => ['Diproses', 'info'],
    'shipped'    => ['Dikirim', 'accent'],
    'delivered'  => ['Selesai', 'good'],
    'cancelled'  => ['Dibatalkan', 'bad'],
];
$status_label_map = [];
foreach ($status_tabs as $k => $v) $status_label_map[$k] = $v[0];
?>

<div class="page-header page-header--wrap">
    <div>
        <h1>Pesanan</h1>
        <p class="page-sub"><?= $kpis['total'] ?> pesanan &middot; <?= rupiah($kpis['revenue']) ?> pembayaran lunas</p>
    </div>
    <a href="/geprek-geh/admin" class="btn btn-ghost btn-sm">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
        Dashboard
    </a>
</div>

<div class="stats-grid stats-grid--orders">
    <div class="stat-card stat-card--rev">
        <span class="stat-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/><path d="M6 12h.01M18 12h.01"/></svg>
        </span>
        <div class="stat-number"><?= rupiah($kpis['revenue']) ?></div>
        <div class="stat-label">Pembayaran Lunas</div>
    </div>
    <div class="stat-card stat-card--order">
        <span class="stat-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        </span>
        <div class="stat-number"><?= $kpis['total'] ?></div>
        <div class="stat-label">Total Pesanan</div>
    </div>
    <div class="stat-card stat-card--pending">
        <span class="stat-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
        </span>
        <div class="stat-number"><?= $kpis['unpaid'] ?></div>
        <div class="stat-label">Menunggu Verifikasi</div>
    </div>
    <div class="stat-card stat-card--prod">
        <span class="stat-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M5 18H3c-.6 0-1-.4-1-1V7c0-.6.4-1 1-1h10c.6 0 1 .4 1 1v11"/><path d="M14 9h4l4 4v4c0 .6-.4 1-1 1h-2M5 18a2 2 0 1 0 4 0 2 2 0 1 0-4 0M13 18a2 2 0 1 0 4 0 2 2 0 1 0-4 0"/><path d="M9 18h4"/></svg>
        </span>
        <div class="stat-number"><?= $kpis['active'] ?></div>
        <div class="stat-label">Dalam Perjalanan</div>
    </div>
</div>

<div class="menu-filters admin-orders-filters">
    <form method="GET" action="/geprek-geh/admin/orders" class="menu-toolbar">
        <div class="menu-filter-row">
            <?php if ($status !== ''): ?><input type="hidden" name="status" value="<?= e($status) ?>"><?php endif; ?>

            <div class="menu-toolbar-top">
                <div class="menu-search">
                    <svg class="menu-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                    <input type="search" name="q" placeholder="Cari invoice, pelanggan, atau produk…" value="<?= e($q) ?>" class="menu-search-input" autocomplete="off" aria-label="Cari pesanan">
                    <?php if ($q !== ''): ?>
                        <a href="/geprek-geh/admin/orders<?= $status !== '' ? '?status=' . urlencode($status) : '' ?>" class="menu-search-clear" aria-label="Bersihkan pencarian">&times;</a>
                    <?php endif; ?>
                </div>

                <span class="menu-sort">
                    <span class="menu-sort-label">Urutkan</span>
                    <span class="menu-sort-box menu-dropdown" data-dropdown data-form-submit>
                        <button type="button" class="menu-dropdown-trigger" data-dropdown-trigger aria-haspopup="listbox" aria-expanded="false">
                            <span data-dropdown-label><?= ['terbaru' => 'Terbaru', 'terlama' => 'Terlama', 'tertinggi' => 'Total Terbesar', 'terendah' => 'Total Terkecil'][$sort] ?? 'Terbaru' ?></span>
                            <svg class="menu-sort-chev menu-sort-chev--js" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                        </button>
                        <div class="menu-dropdown-menu" data-dropdown-menu role="listbox">
                            <button type="button" class="menu-dropdown-item" data-value="terbaru" role="option">Terbaru</button>
                            <button type="button" class="menu-dropdown-item" data-value="terlama" role="option">Terlama</button>
                            <button type="button" class="menu-dropdown-item" data-value="tertinggi" role="option">Total Terbesar</button>
                            <button type="button" class="menu-dropdown-item" data-value="terendah" role="option">Total Terkecil</button>
                        </div>
                        <select name="sort" class="menu-sort-select menu-sort-native" data-dropdown-select onchange="this.form.submit()" aria-label="Urutkan pesanan">
                            <option value="terbaru"<?= $sort === 'terbaru' ? ' selected' : '' ?>>Terbaru</option>
                            <option value="terlama"<?= $sort === 'terlama' ? ' selected' : '' ?>>Terlama</option>
                            <option value="tertinggi"<?= $sort === 'tertinggi' ? ' selected' : '' ?>>Total Terbesar</option>
                            <option value="terendah"<?= $sort === 'terendah' ? ' selected' : '' ?>>Total Terkecil</option>
                        </select>
                        <svg class="menu-sort-chev menu-sort-chev--native" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                    </span>
                </span>
            </div>

            <div class="menu-cat-scroll orders-status-scroll">
                <div class="menu-category-pills">
                    <?php foreach ($status_tabs as $key => [$label, $tone]):
                        $count = $key === '' ? $kpis['total'] : ($status_counts[$key] ?? 0);
                        $pill_params = [];
                        if ($q !== '') $pill_params['q'] = $q;
                        if ($sort !== 'terbaru') $pill_params['sort'] = $sort;
                        if ($key !== '') $pill_params['status'] = $key;
                        $pill_href = '/geprek-geh/admin/orders' . ($pill_params ? '?' . http_build_query($pill_params) : '');
                    ?>
                        <a href="<?= e($pill_href) ?>"
                           class="menu-pill menu-pill--<?= $tone ?: 'all' ?> <?= $status === $key ? 'active' : '' ?>">
                            <?= e($label) ?>
                            <span class="menu-pill-count"><?= $count ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="menu-results">
                <span class="menu-results-text">
                    <?php if ($q !== '' || $status !== ''): ?>
                        <?= $total ?> pesanan
                        <?php if ($q !== ''): ?> untuk "<strong><?= e($q) ?></strong>"<?php endif; ?>
                        <?php if ($status !== ''): ?> di <strong><?= e($status_label_map[$status]) ?></strong><?php endif; ?>
                        — <a href="/geprek-geh/admin/orders" class="menu-results-reset">Reset</a>
                    <?php else: ?>
                        Menampilkan <?= $total ?> pesanan terbaru
                    <?php endif; ?>
                    <span class="menu-results-hint">· <?= $kpis['unpaid'] ?> tagihan belum lunas</span>
                </span>
            </div>
        </div>
    </form>
</div>

<div class="table-wrap">
    <table class="table table--orders">
        <thead>
            <tr>
                <th>Pesanan</th>
                <th>Pelanggan</th>
                <th>Item</th>
                <th>Total</th>
                <th>Bayar</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($orders)): ?>
                <tr>
                    <td colspan="7">
                        <div class="empty-state empty-state--compact">
                            <span class="ghost">📦</span>
                            <h3>Tidak ada pesanan</h3>
                            <p><?= $q !== '' || $status !== '' ? 'Coba ubah kata kunci atau filter status.' : 'Belum ada pesanan masuk.' ?></p>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
            <?php foreach ($orders as $o):
                [$sl, $bc] = format_status($o['status']);
                [$pl, $pc] = format_payment_status($o['payment_status']);
                $pay_method = $o['payment_method'] === 'ewallet' ? 'E-Wallet' : ($o['payment_method'] === 'cod' ? 'COD' : ucfirst($o['payment_method'] ?? '-'));
                $is_unpaid = $o['payment_status'] === 'unpaid';
            ?>
            <tr class="order-row" data-invoice="<?= e($o['invoice_no']) ?>">
                <td>
                    <div class="ocell-invoice">
                        <span class="ocell-no"><?= e($o['invoice_no']) ?></span>
                        <span class="ocell-sub"><?= e(date('d M Y · H:i', strtotime($o['created_at']))) ?></span>
                    </div>
                </td>
                <td>
                    <div class="ocell-cust">
                        <span class="ocell-avatar"><?= e(mb_strtoupper(mb_substr($o['customer_name'], 0, 1))) ?></span>
                        <div class="ocell-cust-main">
                            <span class="ocell-name"><?= e($o['customer_name']) ?></span>
                            <span class="ocell-sub"><?= !empty($o['customer_phone']) ? e($o['customer_phone']) : e($o['customer_email']) ?></span>
                        </div>
                    </div>
                </td>
                <td class="ocell-item"><strong><?= (int) $o['item_count'] ?></strong></td>
                <td class="ocell-total"><?= rupiah(grand_total($o)) ?></td>
                <td>
                    <div class="ocell-pay">
                        <span class="ocell-method"><?= e($pay_method) ?></span>
                        <span class="badge <?= $pc ?>"><?= $pl ?></span>
                    </div>
                </td>
                <td>
                    <?php if ($is_unpaid && !in_array($o['status'], ['cancelled', 'delivered'], true)): ?>
                        <span class="pay-due-dot" title="Tagihan belum lunas"></span>
                    <?php endif; ?>
                    <span class="badge <?= $bc ?>"><?= $sl ?></span>
                </td>
                <td>
                    <a href="/geprek-geh/admin/orders/<?= $o['id'] ?>" class="btn btn-sm btn-outline order-open">
                        Detail
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($total_pages > 1): ?>
<nav class="menu-pagination" aria-label="Navigasi halaman">
    <?php
    $qp = [];
    if ($status !== '') $qp['status'] = $status;
    if ($q !== '') $qp['q'] = $q;
    if ($sort !== 'terbaru') $qp['sort'] = $sort;
    $page_href = function (int $p) use ($qp) {
        return '/geprek-geh/admin/orders?' . http_build_query(['page' => $p] + $qp);
    };
    ?>
    <?php if ($page > 1): ?>
        <a href="<?= e($page_href($page - 1)) ?>" class="menu-page-btn" aria-label="Halaman sebelumnya">&laquo;</a>
    <?php endif; ?>
    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="<?= e($page_href($i)) ?>" class="menu-page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
    <?php if ($page < $total_pages): ?>
        <a href="<?= e($page_href($page + 1)) ?>" class="menu-page-btn" aria-label="Halaman berikutnya">&raquo;</a>
    <?php endif; ?>
</nav>
<?php endif; ?>