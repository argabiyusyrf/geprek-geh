<?php
class InvoiceController {
    public function show($id) {
        Auth::requireLogin();
        $db = Database::getInstance();
        $order = $db->fetchOne(
            "SELECT * FROM orders WHERE id = ? AND user_id = ?",
            [$id, Auth::id()]
        );
        if (!$order) {
            flash_set('error', 'Pesanan tidak ditemukan.');
            redirect('/geprek-geh/orders');
        }

        $items = $db->fetchAll(
            "SELECT oi.*, p.name, p.image
             FROM order_items oi JOIN products p ON oi.product_id = p.id
             WHERE oi.order_id = ?",
            [$id]
        );

        $user = Auth::user();
        $app = require __DIR__ . '/../config/app.php';

        // Render clean invoice HTML (browser print/save-as-PDF)
        header('Content-Type: text/html; charset=utf-8');
        ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice <?= e($order['invoice_no']) ?> — Geprek Geh</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Arial, sans-serif; color: #1d1a15; background: #fff; padding: 32px; max-width: 700px; margin: 0 auto; font-size: 14px; line-height: 1.5; }
        .inv-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 32px; padding-bottom: 24px; border-bottom: 2px solid #e8e0d0; }
        .inv-brand h1 { font-size: 24px; font-weight: 700; letter-spacing: -0.5px; }
        .inv-brand p { color: #8a7a65; font-size: 12px; margin-top: 4px; }
        .inv-meta { text-align: right; }
        .inv-meta .inv-no { font-size: 18px; font-weight: 700; color: #d43e1b; }
        .inv-meta .inv-date { color: #8a7a65; font-size: 13px; margin-top: 4px; }
        .inv-section { margin-bottom: 28px; }
        .inv-section h3 { font-size: 11px; text-transform: uppercase; letter-spacing: 0.15em; color: #8a7a65; margin-bottom: 8px; font-weight: 600; }
        .inv-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .inv-grid p { font-size: 13px; }
        .inv-grid strong { display: block; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th { text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.1em; color: #8a7a65; font-weight: 600; padding: 10px 12px; border-bottom: 2px solid #e8e0d0; }
        th:nth-child(2), th:nth-child(3) { text-align: center; }
        th:last-child { text-align: right; }
        td { padding: 12px; border-bottom: 1px solid #f0ebe0; font-size: 13px; }
        td:nth-child(2), td:nth-child(3) { text-align: center; }
        td:last-child { text-align: right; font-weight: 600; }
        .inv-totals { margin-top: 16px; display: flex; justify-content: flex-end; }
        .inv-totals table { width: auto; min-width: 260px; }
        .inv-totals td { border: none; padding: 6px 12px; font-size: 13px; }
        .inv-totals td:last-child { text-align: right; font-weight: 600; }
        .inv-totals .total-row td { font-size: 16px; font-weight: 700; border-top: 2px solid #1d1a15; padding-top: 10px; }
        .inv-totals .discount-row td { color: #d43e1b; }
        .inv-footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #e8e0d0; text-align: center; color: #8a7a65; font-size: 12px; }
        .inv-footer a { color: #d43e1b; text-decoration: none; }
        .inv-status { display: inline-block; padding: 4px 12px; border-radius: 999px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; }
        .status-pending { background: #fef3cd; color: #856404; }
        .status-processing { background: #d1ecf1; color: #0c5460; }
        .status-shipped { background: #cce5ff; color: #004085; }
        .status-delivered { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .inv-note { background: #faf6ee; border-radius: 12px; padding: 16px; margin-top: 16px; font-size: 13px; color: #5a4d3d; }
        @media print { body { padding: 0; } .no-print { display: none !important; } }
    </style>
</head>
<body>
    <div class="no-print" style="text-align:right;margin-bottom:16px;">
        <button onclick="window.print()" style="padding:8px 20px;background:#d43e1b;color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:13px;font-weight:600;">Cetak / Simpan PDF</button>
    </div>

    <div class="inv-header">
        <div class="inv-brand">
            <h1>Geprek Geh</h1>
            <p>Ayam geprek renyah, sambal level sesuai seleramu.</p>
        </div>
        <div class="inv-meta">
            <div class="inv-no"><?= e($order['invoice_no']) ?></div>
            <div class="inv-date"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></div>
            <div style="margin-top:8px;">
                <?php [$slbl, $scls] = format_status($order['status']); ?>
                <span class="inv-status status-<?= $order['status'] ?>"><?= e($slbl) ?></span>
            </div>
        </div>
    </div>

    <div class="inv-section">
        <h3>Informasi</h3>
        <div class="inv-grid">
            <div>
                <p><strong><?= e($order['recipient_name'] ?? $user['name']) ?></strong></p>
                <p><?= e($user['email']) ?></p>
                <p><?= e($order['shipping_address']) ?></p>
            </div>
            <div>
                <p>Metode Bayar: <strong><?php
                    $pm = ['transfer' => 'Transfer Bank', 'cod' => 'COD', 'ewallet' => 'E-Wallet'];
                    echo e($pm[$order['payment_method']] ?? $order['payment_method']);
                ?></strong></p>
                <p>No. Telepon: <?= e($user['phone'] ?? '-') ?></p>
                <?php if ($order['notes']): ?>
                    <p style="margin-top:8px;font-style:italic;color:#8a7a65;">"<?= e($order['notes']) ?>"</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="inv-section">
        <h3>Item Pesanan</h3>
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $it): ?>
                <tr>
                    <td><?= e($it['name']) ?></td>
                    <td><?= $it['quantity'] ?></td>
                    <td><?= rupiah($it['price']) ?></td>
                    <td><?= rupiah($it['price'] * $it['quantity']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="inv-totals">
        <table>
            <tr>
                <td>Subtotal</td>
                <td><?= rupiah($order['total']) ?></td>
            </tr>
            <?php if (($order['discount'] ?? 0) > 0): ?>
            <tr class="discount-row">
                <td>Diskon<?= $order['promo_code'] ? ' (' . e($order['promo_code']) . ')' : '' ?></td>
                <td>-<?= rupiah($order['discount']) ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td>Pajak (11%)</td>
                <td><?= rupiah($order['tax']) ?></td>
            </tr>
            <tr>
                <td>Ongkir</td>
                <td><?= rupiah($order['shipping_cost']) ?></td>
            </tr>
            <tr class="total-row">
                <td>Total</td>
                <td><?= rupiah(grand_total($order)) ?></td>
            </tr>
        </table>
    </div>

    <div class="inv-footer">
        <p>Terima kasih sudah berbelanja di <a href="/geprek-geh/">Geprek Geh</a>.</p>
        <p style="margin-top:4px;">&copy; <?= date('Y') ?> Geprek Geh. Pesan pedas, antar hangat.</p>
    </div>
</body>
</html>
<?php
        exit;
    }
}
