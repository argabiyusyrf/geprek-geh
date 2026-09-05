<?php
$admin_page_title = 'Kelola Pesanan';
$filter_status = $_GET['status'] ?? '';
?>

<div class="page-header">
    <h1>Pesanan</h1>
</div>

<div class="filter-tabs">
    <a href="/geprek-geh/admin/orders" class="btn btn-sm <?= !$filter_status ? 'btn-primary' : 'btn-outline' ?>">Semua</a>
    <a href="/geprek-geh/admin/orders?status=pending" class="btn btn-sm <?= $filter_status === 'pending' ? 'btn-warning' : 'btn-outline' ?>">Menunggu</a>
    <a href="/geprek-geh/admin/orders?status=processing" class="btn btn-sm <?= $filter_status === 'processing' ? 'btn-info' : 'btn-outline' ?>">Diproses</a>
    <a href="/geprek-geh/admin/orders?status=shipped" class="btn btn-sm <?= $filter_status === 'shipped' ? 'btn-primary' : 'btn-outline' ?>">Dikirim</a>
    <a href="/geprek-geh/admin/orders?status=delivered" class="btn btn-sm <?= $filter_status === 'delivered' ? 'btn-success' : 'btn-outline' ?>">Selesai</a>
    <a href="/geprek-geh/admin/orders?status=cancelled" class="btn btn-sm <?= $filter_status === 'cancelled' ? 'btn-danger' : 'btn-outline' ?>">Dibatalkan</a>
</div>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Invoice</th>
                <th>Pelanggan</th>
                <th>Total</th>
                <th>Bayar</th>
                <th>Status</th>
                <th>Tanggal</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($orders)): ?>
                <tr><td colspan="7" class="text-muted" style="text-align:center">Tidak ada pesanan.</td></tr>
            <?php else: ?>
            <?php foreach ($orders as $o):
                [$sl, $bc] = format_status($o['status']);
            ?>
            <tr>
                <td><strong><?= e($o['invoice_no']) ?></strong></td>
                <td><?= e($o['customer_name']) ?></td>
                <td><?= rupiah($o['grand_total']) ?></td>
                <td>
                    <?= e($o['payment_method'] === 'ewallet' ? 'E-Wallet' : ucfirst($o['payment_method'] ?? '-')) ?>
                    <span class="badge <?= format_payment_status($o['payment_status'])[1] ?>"><?= format_payment_status($o['payment_status'])[0] ?></span>
                </td>
                <td><span class="badge <?= $bc ?>"><?= $sl ?></span></td>
                <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                <td><a href="/geprek-geh/admin/orders/<?= $o['id'] ?>" class="btn btn-sm btn-outline">Detail</a></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($total_pages > 1): ?>
<nav class="order-pagination" aria-label="Navigasi halaman">
    <?php
    $qp = [];
    if ($filter_status !== '') $qp['status'] = $filter_status;
    ?>
    <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1 ?>&<?= http_build_query($qp) ?>" class="menu-page-btn">&laquo;</a>
    <?php endif; ?>
    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=<?= $i ?>&<?= http_build_query($qp) ?>"
           class="menu-page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
    <?php if ($page < $total_pages): ?>
        <a href="?page=<?= $page + 1 ?>&<?= http_build_query($qp) ?>" class="menu-page-btn">&raquo;</a>
    <?php endif; ?>
</nav>
<?php endif; ?>
