<?php
$admin_page_title = 'Detail Pesanan';
[$status_label, $badge_class] = format_status($order['status']);
[$payment_status_label, $payment_badge] = format_payment_status($order['payment_status']);
$bank = $payment_details['bank'] ?? ['name' => '-', 'number' => '-', 'holder' => '-'];
?>

<div class="breadcrumb">
    <a href="/geprek-geh/admin/orders">Pesanan</a> / <span><?= e($order['invoice_no']) ?></span>
</div>

<div class="page-header">
    <h1>Pesanan <?= e($order['invoice_no']) ?></h1>
    <div>
        <span class="badge <?= $badge_class ?>"><?= $status_label ?></span>
        <span class="badge <?= $payment_badge ?>"><?= $payment_status_label ?></span>
    </div>
</div>

<div class="admin-grid-2">
    <div class="card">
        <h3>Info Pelanggan</h3>
        <p><strong>Nama:</strong> <?= e($order['customer_name']) ?></p>
        <p><strong>Email:</strong> <?= e($order['customer_email']) ?></p>
        <p><strong>Telepon:</strong> <?= e($order['customer_phone'] ?? '-') ?></p>
        <p><strong>Alamat:</strong> <?= e($order['shipping_address']) ?></p>
        <p><strong>Metode Bayar:</strong> <?= e($order['payment_method'] === 'ewallet' ? 'E-Wallet (ShopeePay)' : ucfirst($order['payment_method'] ?? '-')) ?></p>
        <p><strong>Catatan:</strong> <?= e($order['notes'] ?? '-') ?></p>

        <?php if ($order['cancel_reason']): ?>
            <p class="cancel-reason"><strong>Alasan Batal:</strong> <?= e($order['cancel_reason']) ?></p>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3>Pembayaran</h3>
        <p>
            Status: <span class="badge <?= $payment_badge ?>"><?= $payment_status_label ?></span>
            <?php if ($order['payment_status'] === 'refunded'): ?><span class="text-muted">— refund diproses</span><?php endif; ?>
        </p>
        <p>
            Instruksi <?= $order['payment_method'] === 'ewallet' ? $bank['name'] . ' / e-wallet' : 'transfer' ?>:
            <?= $order['payment_method'] === 'transfer' ? e($bank['name']) . ' • ' . e($bank['number']) . ' a.n. ' . e($bank['holder']) : e($bank['name']) . ' • ' . e($bank['number']) . ' a.n. ' . e($bank['holder']) ?>
        </p>

        <?php if ($order['payment_proof']): ?>
            <div class="proof-section">
                <h4>Bukti Pembayaran</h4>
                <img src="/geprek-geh/assets/uploads/payments/<?= e($order['payment_proof']) ?>" class="proof-img" alt="Bukti">
            </div>
        <?php elseif (in_array($order['payment_method'], ['transfer', 'ewallet'], true)): ?>
            <p class="text-muted">Belum ada bukti pembayaran.</p>
        <?php else: ?>
            <p class="text-muted">COD — lunas otomatis saat pesanan diterima.</p>
        <?php endif; ?>

        <?php
            $can_verify = in_array($order['payment_method'], ['transfer', 'ewallet'], true)
                && $order['payment_status'] !== 'paid'
                && $order['payment_status'] !== 'refunded'
                && $order['payment_proof']
                && !in_array($order['status'], ['cancelled', 'delivered'], true);
        ?>
        <?php if ($can_verify): ?>
        <form method="POST" action="/geprek-geh/admin/orders/<?= $order['id'] ?>/verify-payment" data-confirm="Tandai pembayaran pesanan ini LUNAS? Status akan otomatis lanjut ke 'Diproses' bila masih menunggu.">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-success" style="margin-top:8px">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                Tandai Lunas (Verifikasi)
            </button>
        </form>
        <?php endif; ?>
    </div>
</div>

<div class="admin-grid-2">
    <div class="card">
        <h3>Ringkasan</h3>
        <p>Subtotal: <?= rupiah($order['total']) ?></p>
        <p>Ongkir: <?= rupiah($order['shipping_cost']) ?></p>
        <p>Pajak: <?= rupiah($order['tax']) ?></p>
        <p class="order-grand-total"><strong>Grand Total: <?= rupiah($order['grand_total']) ?></strong></p>
    </div>

    <div class="card">
        <h3>Perbarui Status</h3>
        <p class="text-muted">Status saat ini: <strong><?= $status_label ?></strong></p>

        <?php if (empty($transitions)): ?>
            <p class="text-muted">Tidak ada transisi status yang tersedia (status terminal).</p>
        <?php else: ?>
        <form method="POST" action="/geprek-geh/admin/orders/<?= $order['id'] ?>/status" data-status-form>
            <?= csrf_field() ?>
            <select name="status" class="input" data-status-select required>
                <option value="" disabled selected>Pilih status berikutnya…</option>
                <?php foreach ($transitions as $t): [$tl] = format_status($t); ?>
                    <option value="<?= $t ?>"><?= $tl ?></option>
                <?php endforeach; ?>
            </select>

            <div class="status-extra" data-status-extra="shipped" hidden>
                <label>Nomor Resi (opsional) <span class="label-optional"></span></label>
                <input type="text" name="tracking_no" class="input" placeholder="Contoh: JNE-FFC-000123456" maxlength="80">
            </div>

            <div class="status-extra" data-status-extra="cancelled" hidden>
                <label>Alasan Pembatalan *</label>
                <textarea name="cancel_reason" class="input" rows="2" placeholder="Wajib diisi — alasan ini terlihat oleh pelanggan" maxlength="255"></textarea>
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top:12px">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                Simpan Status
            </button>
        </form>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <h3>Item Pesanan</h3>
    <table class="table">
        <thead><tr><th>Produk</th><th>Harga</th><th>Qty</th><th>Subtotal</th></tr></thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td><?= e($item['name']) ?></td>
                <td><?= rupiah($item['price']) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td><strong><?= rupiah($item['price'] * $item['quantity']) ?></strong></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if (!empty($logs)): ?>
<div class="card">
    <h3>Riwayat Pesanan</h3>
    <div class="log-list">
        <?php foreach ($logs as $log): ?>
        <div class="log-item">
            <span class="log-dot log-dot--<?= $log['actor'] ?>"></span>
            <span class="log-msg"><?= e($log['message']) ?></span>
            <span class="log-time"><?= date('d M Y, H:i', strtotime($log['created_at'])) ?> · <?= $log['actor'] === 'admin' ? 'Admin' : 'Pelanggan' ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>