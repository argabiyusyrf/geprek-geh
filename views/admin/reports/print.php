<?php
// Layout mandiri untuk cetak laporan penjualan.
$method_label = fn($m) => \payment_method_label($m);
$grand_revenue = (int) $kpis['revenue'];
$total_orders = (int) $kpis['total_orders'];
$total_qty = (int) $kpis['total_qty'];
$fmt = fn($n) => number_format((int) round($n), 0, ',', '.');
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Laporan Penjualan — Geprek Geh</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; background: #e8e4dd; color: #1d1a15; }
    .toolbar { position: sticky; top: 0; z-index: 10; display: flex; gap: 10px; justify-content: center; padding: 14px; background: #1d1a15; box-shadow: 0 4px 18px rgba(0,0,0,.18); }
    .toolbar button { border: none; border-radius: 999px; padding: 10px 22px; font-size: 14px; font-weight: 700; cursor: pointer; }
    .btn-print { background: #f04e2c; color: #fff; }
    .btn-close { background: #fff; color: #1d1a15; }
    .sheet { max-width: 820px; margin: 28px auto; background: #fff; border-radius: 4px; padding: 46px 52px; box-shadow: 0 10px 40px rgba(0,0,0,.12); }
    .s-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; border-bottom: 3px solid #1d1a15; padding-bottom: 18px; }
    .s-brand { font-size: 22px; font-weight: 900; }
    .s-brand span { color: #e04a26; }
    .s-brand-sub { font-size: 11px; color: #88807a; letter-spacing: .14em; text-transform: uppercase; margin-top: 3px; }
    .s-invoice { text-align: right; font-size: 15px; }
    .s-invoice-no { font-weight: 800; }
    h2 { font-size: 16px; margin: 30px 0 12px; padding-bottom: 8px; border-bottom: 2px solid #1d1a15; text-transform: uppercase; letter-spacing: .04em; }
    h2:first-of-type { margin-top: 24px; }
    table { width: 100%; border-collapse: collapse; font-size: 13px; }
    th, td { padding: 7px 10px; text-align: left; border-bottom: 1px solid #ddd8d0; }
    th { font-size: 11px; text-transform: uppercase; letter-spacing: .05em; color: #88807a; }
    td.r, th.r { text-align: right; }
    .kpis { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-top: 24px; }
    .kpi { border: 1px solid #ddd8d0; border-radius: 8px; padding: 14px; }
    .kpi b { display: block; font-size: 17px; margin-top: 4px; }
    .kpi span { font-size: 11px; color: #88807a; text-transform: uppercase; letter-spacing: .08em; }
    .total-row { font-weight: 800; }
    .meta { margin-top: 26px; font-size: 12px; color: #88807a; padding-top: 14px; border-top: 1px dashed #ccc; }
    @media print {
        .toolbar { display: none; }
        body { background: #fff; }
        .sheet { box-shadow: none; margin: 0 auto; padding: 20px 0; max-width: none; }
        .kpis { break-inside: avoid; }
        h2 { break-after: avoid; }
        table { break-inside: auto; }
        tr { break-inside: avoid; }
    }
</style>
</head>
<body>
<div class="toolbar">
    <button class="btn-print" onclick="window.print()">Cetak Laporan</button>
    <button class="btn-close" onclick="window.close()">Tutup</button>
</div>

<div class="sheet">
    <div class="s-head">
        <div>
            <div class="s-brand">Geprek <span>Geh</span></div>
            <div class="s-brand-sub">Laporan Penjualan</div>
        </div>
        <div class="s-invoice">
            <div class="s-invoice-no"><?= e($from) ?> s/d <?= e($to) ?></div>
            <div class="s-brand-sub">Dicetak <?= e(date('d M Y H:i')) ?></div>
        </div>
    </div>

    <div class="kpis">
        <div class="kpi"><span>Omzet</span><b>Rp<?= $fmt($grand_revenue) ?></b></div>
        <div class="kpi"><span>Pesanan</span><b><?= $total_orders ?></b></div>
        <div class="kpi"><span>Item Terjual</span><b><?= $total_qty ?></b></div>
        <div class="kpi"><span>Rata-rata</span><b>Rp<?= $fmt($kpis['avg_order']) ?></b></div>
    </div>

    <h2>Penjualan per Hari</h2>
    <?php if (empty($daily)): ?>
        <p style="color:#88807a">Belum ada penjualan pada periode ini.</p>
    <?php else: ?>
    <table>
        <thead><tr><th>Tanggal</th><th class="r">Pesanan</th><th class="r">Qty</th><th class="r">Omzet (Rp)</th></tr></thead>
        <tbody>
        <?php foreach ($daily as $r): ?>
            <tr><td><?= e(date('d M Y', strtotime($r['tgl']))) ?></td><td class="r"><?= (int) $r['order_count'] ?></td><td class="r"><?= (int) $r['qty'] ?></td><td class="r"><?= $fmt($r['revenue']) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <h2>Produk Terlaris</h2>
    <?php if (empty($top_products)): ?>
        <p style="color:#88807a">Belum ada data produk terjual.</p>
    <?php else: ?>
    <table>
        <thead><tr><th>Produk</th><th class="r">Qty</th><th class="r">Omzet (Rp)</th></tr></thead>
        <tbody>
        <?php foreach ($top_products as $r): ?>
            <tr><td><?= e($r['name']) ?></td><td class="r"><?= (int) $r['qty'] ?></td><td class="r"><?= $fmt($r['revenue']) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <h2>Omzet per Kategori</h2>
    <?php if (empty($by_category)): ?>
        <p style="color:#88807a">Belum ada data.</p>
    <?php else: ?>
    <table>
        <thead><tr><th>Kategori</th><th class="r">Qty</th><th class="r">Omzet (Rp)</th></tr></thead>
        <tbody>
        <?php foreach ($by_category as $r): ?>
            <tr><td><?= e($r['name']) ?></td><td class="r"><?= (int) $r['qty'] ?></td><td class="r"><?= $fmt($r['revenue']) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <h2>Metode Pembayaran</h2>
    <?php if (empty($by_method)): ?>
        <p style="color:#88807a">Belum ada data.</p>
    <?php else: ?>
    <table>
        <thead><tr><th>Metode</th><th class="r">Pesanan</th><th class="r">Omzet (Rp)</th></tr></thead>
        <tbody>
        <?php foreach ($by_method as $r): ?>
            <tr><td><?= e($method_label($r['payment_method'])) ?></td><td class="r"><?= (int) $r['cnt'] ?></td><td class="r"><?= $fmt($r['revenue']) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <p class="meta">Laporan hanya menghitung pesanan yang tidak dibatalkan. Data sewaktu-waktu dapat berubah seiring aktivitas toko (misal: pembatalan otomatis).</p>
</div>
</body>
</html>