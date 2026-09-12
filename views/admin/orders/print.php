<?php
[$status_label] = \format_status($order['status']);
[$payment_status_label] = \format_payment_status($order['payment_status']);
$method = $order['payment_method'] ?? '';
$method_label = $method === 'ewallet' ? 'E-Wallet (ShopeePay)' : ($method === 'cod' ? 'COD — Bayar di Tempat' : ($method === 'transfer' ? 'Transfer Bank' : ucfirst($method ?: '-')));
$grand = \grand_total($order);
$created = date('d M Y, H:i', strtotime($order['created_at']));
$bank = $payment_details['bank'] ?? ['name' => '-', 'number' => '-', 'holder' => '-'];
$proof_exists = !empty($order['payment_proof']) && file_exists(dirname(__DIR__, 3) . '/assets/uploads/payments/' . $order['payment_proof']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Cetak <?= e($order['invoice_no']) ?> — Geprek Geh</title>
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; background: #e8e4dd; color: #1d1a15; }
    .toolbar {
        position: sticky; top: 0; z-index: 10;
        display: flex; align-items: center; gap: 10px; justify-content: center;
        padding: 14px; background: #1d1a15; box-shadow: 0 4px 18px rgba(0,0,0,.18);
    }
    .toolbar button {
        border: none; border-radius: 999px; padding: 10px 22px; font-size: 14px; font-weight: 700; cursor: pointer;
    }
    .btn-print { background: #f04e2c; color: #fff; }
    .btn-close { background: #fff; color: #1d1a15; }
    .sheet {
        max-width: 780px; margin: 28px auto; background: #fff; border-radius: 4px;
        padding: 46px 52px; box-shadow: 0 10px 40px rgba(0,0,0,.12);
    }
    .s-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; border-bottom: 3px solid #1d1a15; padding-bottom: 20px; }
    .s-brand { font-size: 22px; font-weight: 900; letter-spacing: .02em; }
    .s-brand span { color: #e04a26; }
    .s-brand-sub { font-size: 11px; color: #88807a; letter-spacing: .14em; text-transform: uppercase; margin-top: 3px; }
    .s-invoice { text-align: right; }
    .s-invoice-no { font-size: 17px; font-weight: 800; letter-spacing: .02em; }
    .s-invoice-tag { display: inline-block; margin-top: 6px; font-size: 11px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; padding: 3px 10px; border-radius: 999px; }
    .tag-paid { background: #e6f4ea; color: #1d6b36; }
    .tag-unpaid { background: #fdf3df; color: #a16207; }
    .s-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 26px 30px; padding: 24px 0; margin: 0 0 -8px; }
    .s-meta h4 { font-size: 10.5px; letter-spacing: .12em; text-transform: uppercase; color: #9a9088; margin-bottom: 7px; }
    .s-meta .v { font-size: 13.5px; line-height: 1.6; }
    .s-meta .v strong { color: #1d1a15; }
    .s-table { width: 100%; border-collapse: collapse; margin-top: 18px; }
    .s-table th { text-align: left; font-size: 10.5px; letter-spacing: .12em; text-transform: uppercase; color: #9a9088; padding: 10px 12px; border-bottom: 1.5px solid #e5e0d8; }
    .s-table td { padding: 12px; font-size: 13.5px; border-bottom: 1px solid #eeeae3; vertical-align: top; }
    .s-table td.num, .s-table th.num { text-align: right; white-space: nowrap; }
    .s-item-name { font-weight: 700; }
    .s-item-meta { color: #8f867d; font-size: 12px; margin-top: 2px; }
    .s-side { display: grid; grid-template-columns: 1fr 240px; gap: 30px; margin-top: 26px; }
    .s-info h4 { font-size: 10.5px; letter-spacing: .12em; text-transform: uppercase; color: #9a9088; margin-bottom: 8px; }
    .s-info p { font-size: 13px; line-height: 1.65; }
    .s-totals { border: 1px solid #e5e0d8; border-radius: 4px; padding: 14px 18px; align-self: start; }
    .s-line { display: flex; justify-content: space-between; font-size: 13px; padding: 5px 0; color: #4a443c; }
    .s-line.grand { border-top: 1.5px solid #1d1a15; margin-top: 7px; padding-top: 10px; font-size: 15px; font-weight: 800; color: #1d1a15; }
    .s-foot { margin-top: 34px; border-top: 1.5px solid #e5e0d8; padding-top: 14px; text-align: center; font-size: 11.5px; color: #9a9088; line-height: 1.8; }
    @media print {
        body { background: #fff; }
        .toolbar { display: none; }
        .sheet { margin: 0 auto; box-shadow: none; padding: 0; max-width: 100%; border-radius: 0; }
    }
</style>
</head>
<body>

<nav class="toolbar">
    <button class="btn-print" onclick="window.print()">Cetak / Simpan PDF</button>
    <button class="btn-close" onclick="window.close()">Tutup</button>
</nav>

<main class="sheet">
    <header class="s-head">
        <div class="s-brand">Geprek<span> Geh</span>
            <div class="s-brand-sub">Invoice Pesanan</div>
        </div>
        <div class="s-invoice">
            <div class="s-invoice-no"><?= e($order['invoice_no']) ?></div>
            <span class="s-invoice-tag <?= $order['payment_status'] === 'paid' ? 'tag-paid' : 'tag-unpaid' ?>"><?= $payment_status_label ?></span>
        </div>
    </header>

    <div class="s-meta">
        <div>
            <h4>Dibuat</h4>
            <div class="v"><?= e($created) ?></div>
        </div>
        <div>
            <h4>Status</h4>
            <div class="v"><strong><?= $status_label ?></strong><?= !empty($order['tracking_no']) ? ' &middot; Resi ' . e($order['tracking_no']) : '' ?></div>
        </div>
        <div>
            <h4>Pelanggan</h4>
            <div class="v"><strong><?= e($order['customer_name']) ?></strong><br><?= e($order['customer_email']) ?><br><?= e($order['customer_phone']) ?></div>
        </div>
        <div>
            <h4>Alamat Pengiriman</h4>
            <div class="v"><?= $order['shipping_address'] ? nl2br(e($order['shipping_address'])) : '-' ?></div>
        </div>
    </div>

    <table class="s-table">
        <thead>
            <tr><th>Item</th><th class="num">Qty</th><th class="num">Harga</th><th class="num">Subtotal</th></tr>
        </thead>
        <tbody>
            <?php if (empty($items)): ?>
                <tr><td colspan="4">Item tidak tersedia.</td></tr>
            <?php else: foreach ($items as $item):
                $sub = (int) $item['price'] * (int) $item['quantity']; ?>
                <tr>
                    <td><div class="s-item-name"><?= e($item['name']) ?></div><div class="s-item-meta">SKU-<?= e($item['product_id']) ?></div></td>
                    <td class="num"><?= (int) $item['quantity'] ?></td>
                    <td class="num"><?= \rupiah($item['price']) ?></td>
                    <td class="num"><?= \rupiah($sub) ?></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>

    <div class="s-side">
        <div class="s-info">
            <h4>Pembayaran</h4>
            <p>
                <strong><?= e($method_label) ?></strong><br>
                <?php if (!in_array($method, ['cod'], true)): ?>
                    <?= e($bank['name']) ?> &bull; <?= e($bank['number']) ?> a.n. <?= e($bank['holder']) ?><br>
                    Status: <strong><?= $payment_status_label ?></strong>
                <?php else: ?>
                    Status: <strong><?= $payment_status_label ?></strong>
                <?php endif; ?>
            </p>
            <?php if (!empty($order['notes'])): ?>
                <h4 style="margin-top:16px;">Catatan</h4>
                <p><?= e($order['notes']) ?></p>
            <?php endif; ?>
        </div>
        <div class="s-totals">
            <div class="s-line"><span>Subtotal</span><span><?= \rupiah($order['total']) ?></span></div>
            <?php if ((int) $order['discount'] > 0): ?>
                <div class="s-line"><span>Diskon<?= $order['promo_code'] ? ' (' . e($order['promo_code']) . ')' : '' ?></span><span>&minus; <?= \rupiah($order['discount']) ?></span></div>
            <?php endif; ?>
            <div class="s-line"><span>Ongkos Kirim</span><span><?= \rupiah($order['shipping_cost']) ?></span></div>
            <div class="s-line"><span>Pajak</span><span><?= \rupiah($order['tax']) ?></span></div>
            <div class="s-line grand"><span>Grand Total</span><span><?= \rupiah($grand) ?></span></div>
        </div>
    </div>

    <footer class="s-foot">
        Terima kasih sudah berbelanja di Geprek Geh &mdash; <?= e($order['invoice_no']) ?><br>
        Dicetak <?= date('d M Y, H:i') ?>
    </footer>
</main>

<script>
    window.addEventListener('load', () => setTimeout(() => window.print(), 350));
</script>
</body>
</html>