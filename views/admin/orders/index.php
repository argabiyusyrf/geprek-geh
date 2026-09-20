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

$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));
$date_active = $from !== '' || $to !== '';
$presets = [
    'Hari Ini'  => [$today, $today],
    'Kemarin'   => [$yesterday, $yesterday],
    '7 Hari'    => [date('Y-m-d', strtotime('-6 days')), $today],
    '30 Hari'   => [date('Y-m-d', strtotime('-29 days')), $today],
    'Bulan Ini' => [date('Y-m-01'), $today],
];
$extra_q = [];
if ($q !== '') $extra_q['q'] = $q;
if ($sort !== 'terbaru') $extra_q['sort'] = $sort;
if ($per !== 15) $extra_q['per'] = $per;
$period_display = '';
if ($date_active) {
    $period_display = ($from !== '' ? date('d M Y', strtotime($from)) : 'sebelumnya')
        . ($from !== $to && $to !== '' ? ' — ' . date('d M Y', strtotime($to)) : '');
}
$clear_params = $extra_q;
unset($clear_params['q']);
if ($from !== '') $clear_params['from'] = $from;
if ($to !== '') $clear_params['to'] = $to;
if ($status !== '') $clear_params['status'] = $status;
$search_clear_href = '/geprek-geh/admin/orders' . ($clear_params ? '?' . http_build_query($clear_params) : '');
$date_reset_params = $extra_q;
if ($status !== '') $date_reset_params['status'] = $status;
$date_reset_href = '/geprek-geh/admin/orders' . ($date_reset_params ? '?' . http_build_query($date_reset_params) : '');
$preset_href = function (string $pf, string $pt) use ($extra_q, $status) {
    $qp = $extra_q;
    $qp['from'] = $pf;
    $qp['to'] = $pt;
    if ($status !== '') $qp['status'] = $status;
    return '/geprek-geh/admin/orders?' . http_build_query($qp);
};
$page_window = [];
if ($total_pages <= 7) {
    $page_window = range(1, $total_pages);
} else {
    $page_window[] = 1;
    $ws = max(2, $page - 2);
    $we = min($total_pages - 1, $page + 2);
    if ($ws > 2) $page_window[] = null;
    for ($i = $ws; $i <= $we; $i++) $page_window[] = $i;
    if ($we < $total_pages - 1) $page_window[] = null;
    $page_window[] = $total_pages;
}
$qp = $extra_q;
if ($from !== '') $qp['from'] = $from;
if ($to !== '') $qp['to'] = $to;
if ($status !== '') $qp['status'] = $status;
$page_href = function (int $p) use ($qp) {
    return '/geprek-geh/admin/orders?' . http_build_query(['page' => $p] + $qp);
};
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
            <?php if ($per !== 15): ?><input type="hidden" name="per" value="<?= e($per) ?>"><?php endif; ?>

            <div class="menu-toolbar-top">
                <div class="menu-search">
                    <svg class="menu-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                    <input type="search" name="q" placeholder="Cari invoice, pelanggan, atau produk…" value="<?= e($q) ?>" class="menu-search-input" autocomplete="off" aria-label="Cari pesanan">
                    <?php if ($q !== ''): ?>
                        <a href="<?= e($search_clear_href) ?>" class="menu-search-clear" aria-label="Bersihkan pencarian">&times;</a>
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

            <div class="menu-date-row">
                <div class="menu-date-presets" role="group" aria-label="Filter periode">
                    <span class="menu-sort-label">Periode</span>
                    <a href="<?= e($date_reset_href) ?>" class="menu-pill menu-pill--all <?= !$date_active ? 'active' : '' ?>">Semua</a>
                    <?php foreach ($presets as $label => [$pf, $pt]): ?>
                        <a href="<?= e($preset_href($pf, $pt)) ?>" class="menu-pill <?= $from === $pf && $to === $pt ? 'active' : '' ?>">
                            <?= e($label) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                <div class="menu-date-custom">
                    <input type="date" name="from" value="<?= e($from) ?>" class="input menu-date-input" aria-label="Tanggal mulai">
                    <span class="menu-date-sep">s.d.</span>
                    <input type="date" name="to" value="<?= e($to) ?>" class="input menu-date-input" aria-label="Tanggal akhir">
                    <button type="submit" class="btn btn-sm btn-ghost menu-date-apply">Terapkan</button>
                    <?php if ($date_active): ?>
                        <a href="<?= e($date_reset_href) ?>" class="menu-search-clear menu-date-clear" aria-label="Reset tanggal">&times;</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="menu-cat-scroll orders-status-scroll">
                <div class="menu-category-pills">
                    <?php foreach ($status_tabs as $key => [$label, $tone]):
                        $count = $key === '' ? $kpis['total'] : ($status_counts[$key] ?? 0);
                        $pill_params = $extra_q;
                        if ($from !== '') $pill_params['from'] = $from;
                        if ($to !== '') $pill_params['to'] = $to;
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
                    <?php if ($q !== '' || $status !== '' || $date_active): ?>
                        <?= $total ?> pesanan
                        <?php if ($q !== ''): ?> untuk "<strong><?= e($q) ?></strong>"<?php endif; ?>
                        <?php if ($status !== ''): ?> di <strong><?= e($status_label_map[$status]) ?></strong><?php endif; ?>
                        <?php if ($date_active): ?> periode <strong><?= e($period_display) ?></strong><?php endif; ?>
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
                            <span class="ghost"><svg style="width:4.5rem;height:4.5rem" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg></span>
                            <h3>Tidak ada pesanan</h3>
                            <p><?= $q !== '' || $status !== '' || $date_active ? 'Coba ubah kata kunci, status, atau periode tanggal.' : 'Belum ada pesanan masuk.' ?></p>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
            <?php foreach ($orders as $o):
                [$sl, $bc] = format_status($o['status']);
                [$pl, $pc] = format_payment_status($o['payment_status']);
                $pay_method = \payment_method_label($o['payment_method']);
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

<?php if ($total > 0): ?>
<nav class="menu-pagination menu-pagination--orders" aria-label="Navigasi halaman">
    <?php if ($total_pages > 1): ?>
        <div class="menu-pagination-pages">
            <?php if ($page > 1): ?>
                <a href="<?= e($page_href($page - 1)) ?>" class="menu-page-btn" aria-label="Halaman sebelumnya">&laquo;</a>
            <?php endif; ?>
            <?php foreach ($page_window as $pw): ?>
                <?php if ($pw === null): ?>
                    <span class="menu-page-dots" aria-hidden="true">&hellip;</span>
                <?php else: ?>
                    <a href="<?= e($page_href($pw)) ?>" class="menu-page-btn <?= $pw === $page ? 'active' : '' ?>"><?= $pw ?></a>
                <?php endif; ?>
            <?php endforeach; ?>
            <?php if ($page < $total_pages): ?>
                <a href="<?= e($page_href($page + 1)) ?>" class="menu-page-btn" aria-label="Halaman berikutnya">&raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <form method="GET" action="/geprek-geh/admin/orders" class="menu-perpage">
        <?php if ($status !== ''): ?><input type="hidden" name="status" value="<?= e($status) ?>"><?php endif; ?>
        <?php if ($q !== ''): ?><input type="hidden" name="q" value="<?= e($q) ?>"><?php endif; ?>
        <?php if ($sort !== 'terbaru'): ?><input type="hidden" name="sort" value="<?= e($sort) ?>"><?php endif; ?>
        <?php if ($from !== ''): ?><input type="hidden" name="from" value="<?= e($from) ?>"><?php endif; ?>
        <?php if ($to !== ''): ?><input type="hidden" name="to" value="<?= e($to) ?>"><?php endif; ?>
        <label for="orders-per-page" class="menu-perpage-label">Baris per halaman</label>
        <select id="orders-per-page" name="per" class="menu-perpage-select" onchange="this.form.submit()" aria-label="Baris per halaman">
            <?php foreach ([10, 15, 25, 50] as $pp): ?>
                <option value="<?= $pp ?>"<?= $per === $pp ? ' selected' : '' ?>><?= $pp ?> baris</option>
            <?php endforeach; ?>
        </select>
    </form>
</nav>
<?php endif; ?>