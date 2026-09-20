<?php
$method_label = fn($m) => \payment_method_label($m);
?>

<div class="breadcrumb">
    <a href="/admin">Dashboard</a>
    <span>/</span>
    <span>Laporan Penjualan</span>
</div>

<div class="page-header page-header--wrap">
    <div>
        <h1>Laporan Penjualan</h1>
        <p class="page-sub">Ringkasan penjualan <?= e($from) ?> s/d <?= e($to) ?> (pesanan yang tidak dibatalkan)</p>
    </div>
    <div class="order-head-actions">
        <a href="/admin/reports/print?from=<?= e($from) ?>&to=<?= e($to) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-ghost">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>
            Cetak
        </a>
        <a href="/admin/reports/export?from=<?= e($from) ?>&to=<?= e($to) ?>" class="btn btn-primary btn-sm">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
            Export CSV
        </a>
    </div>
</div>

<form method="GET" action="/admin/reports" class="menu-toolbar reports-filter">
    <div class="menu-filter-row">
        <div class="form-row">
            <div class="form-group">
                <label for="report-from">Dari Tanggal</label>
                <input type="date" id="report-from" name="from" class="input" value="<?= e($from) ?>">
            </div>
            <div class="form-group">
                <label for="report-to">Sampai Tanggal</label>
                <input type="date" id="report-to" name="to" class="input" value="<?= e($to) ?>">
            </div>
            <div class="form-group reports-filter-submit">
                <button type="submit" class="btn btn-ghost">Tampilkan</button>
            </div>
        </div>
    </div>
</form>

<div class="stats-grid stats-grid--reports">
    <div class="stat-card stat-card--rev">
        <span class="stat-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </span>
        <div class="stat-number"><?= rupiah((int) $kpis['revenue']) ?></div>
        <div class="stat-label">Omzet</div>
    </div>
    <div class="stat-card stat-card--order">
        <span class="stat-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        </span>
        <div class="stat-number"><?= (int) $kpis['total_orders'] ?></div>
        <div class="stat-label">Pesanan</div>
    </div>
    <div class="stat-card stat-card--cust">
        <span class="stat-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
        </span>
        <div class="stat-number"><?= (int) $kpis['total_qty'] ?></div>
        <div class="stat-label">Item Terjual</div>
    </div>
    <div class="stat-card stat-card--pending">
        <span class="stat-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 15l4-6 4 3 4-8"/></svg>
        </span>
        <div class="stat-number"><?= rupiah((int) round($kpis['avg_order'])) ?></div>
        <div class="stat-label">Rata-rata per Pesanan</div>
    </div>
</div>

<div class="admin-grid-2">
    <div class="card order-card">
        <header class="order-card-head">
            <h3>Penjualan per Hari</h3>
        </header>
        <?php if (empty($daily)): ?>
            <div class="empty-state empty-state--compact">
                <span class="ghost"><svg style="width:4.5rem;height:4.5rem" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="12" width="4" height="9" rx="1"/><rect x="10" y="7" width="4" height="14" rx="1"/><rect x="17" y="3" width="4" height="18" rx="1"/></svg></span>
                <h3>Belum ada penjualan</h3>
                <p>Tidak ada pesanan pada periode ini.</p>
            </div>
        <?php else: ?>
        <?php
        $max_rev = (int) max(array_column($chart, 'revenue'));
        $max_rev = $max_rev > 0 ? $max_rev : 1;
        ?>
        <div class="report-chart" role="img" aria-label="Grafik omzet per hari">
            <?php foreach ($chart as $c): $rev = (int) $c['revenue']; $h = max(4, round(($rev / $max_rev) * 100)); ?>
                <div class="report-chart-col">
                    <div class="report-chart-meta"><?= $rev > 0 ? '<span class="report-chart-val">' . rupiah($rev) . '</span>' : '' ?></div>
                    <div class="report-chart-bar" style="height: <?= $h ?>%" title="<?= e($c['tgl']) ?> · <?= rupiah($rev) ?>"></div>
                    <span class="report-chart-label"><?= e(date('d/m', strtotime($c['tgl']))) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Pesanan</th>
                        <th>Qty</th>
                        <th>Omzet</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($daily as $r): ?>
                    <tr>
                        <td class="stock-cell"><?= e($r['tgl']) ?></td>
                        <td><?= (int) $r['order_count'] ?></td>
                        <td class="stock-cell"><?= (int) $r['qty'] ?></td>
                        <td><?= rupiah((int) $r['revenue']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <div class="card order-card">
        <header class="order-card-head">
            <h3>Produk Terlaris</h3>
        </header>
        <?php if (empty($top_products)): ?>
            <p class="text-muted">Belum ada data produk terjual.</p>
        <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Qty</th>
                        <th>Omzet</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($top_products as $r): ?>
                    <tr>
                        <td class="ocell-invoice">
                            <span class="ocell-no"><?= e($r['name']) ?></span>
                        </td>
                        <td class="stock-cell"><?= (int) $r['qty'] ?></td>
                        <td><?= rupiah((int) $r['revenue']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <header class="order-card-head report-head-gap">
            <h3>Omzet per Kategori</h3>
        </header>
        <?php if (empty($by_category)): ?>
            <p class="text-muted">Belum ada data.</p>
        <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Kategori</th>
                        <th>Qty</th>
                        <th>Omzet</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($by_category as $r): ?>
                    <tr>
                        <td><?= e($r['name']) ?></td>
                        <td class="stock-cell"><?= (int) $r['qty'] ?></td>
                        <td><?= rupiah((int) $r['revenue']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <header class="order-card-head report-head-gap">
            <h3>Metode Pembayaran</h3>
        </header>
        <?php if (empty($by_method)): ?>
            <p class="text-muted">Belum ada data.</p>
        <?php else: ?>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Metode</th>
                        <th>Pesanan</th>
                        <th>Omzet</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($by_method as $r): ?>
                    <tr>
                        <td><?= e($method_label($r['payment_method'])) ?></td>
                        <td class="stock-cell"><?= (int) $r['cnt'] ?></td>
                        <td><?= rupiah((int) $r['revenue']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>