<?php $admin_page_title = 'Dashboard'; ?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number"><?= rupiah($stats['revenue']) ?></div>
        <div class="stat-label">Total Pendapatan</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $stats['orders'] ?></div>
        <div class="stat-label">Total Pesanan</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $stats['pending'] ?></div>
        <div class="stat-label">Pesanan Pending</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $stats['products'] ?></div>
        <div class="stat-label">Total Produk</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $stats['customers'] ?></div>
        <div class="stat-label">Total Pelanggan</div>
    </div>
</div>

<div class="card">
    <h3>Pesanan Terbaru</h3>
    <?php if (empty($recent_orders)): ?>
        <p class="text-muted">Belum ada pesanan.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Pelanggan</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_orders as $o):
                    [$sl, $bc] = format_status($o['status']);
                ?>
                <tr>
                    <td><strong><?= e($o['invoice_no']) ?></strong></td>
                    <td><?= e($o['customer_name']) ?></td>
                    <td><?= rupiah($o['grand_total']) ?></td>
                    <td><span class="badge <?= $bc ?>"><?= $sl ?></span></td>
                    <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                    <td><a href="/geprek-geh/admin/orders/<?= $o['id'] ?>" class="btn btn-sm btn-outline">Detail</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
