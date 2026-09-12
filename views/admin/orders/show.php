<?php
$admin_page_title = 'Detail Pesanan';
[$status_label, $badge_class] = format_status($order['status']);
[$payment_status_label, $payment_badge] = format_payment_status($order['payment_status']);
$bank = $payment_details['bank'] ?? ['name' => '-', 'number' => '-', 'holder' => '-'];
$method = $order['payment_method'] ?? '';
$method_label = $method === 'ewallet' ? 'E-Wallet (ShopeePay)' : ($method === 'cod' ? 'COD — Bayar di Tempat' : ($method === 'transfer' ? 'Transfer Bank' : ucfirst($method ?: '-')));
$grand = grand_total($order);
$is_cod = $method === 'cod';
$created = date('d M Y, H:i', strtotime($order['created_at']));
$total_qty = array_sum(array_map(fn($it) => (int) $it['quantity'], $items));
$wa = !empty($order['customer_phone']) ? \wa_link($order['customer_phone']) : null;
$proof_exists = !empty($order['payment_proof']) && file_exists(dirname(__DIR__, 3) . '/assets/uploads/payments/' . $order['payment_proof']);

$timeline = [
    'pending'    => ['Menunggu Konfirmasi', 'Pesanan masuk — cek bukti pembayaran'],
    'processing' => ['Diproses', 'Dikonfirmasi & masuk dapur'],
    'shipped'    => ['Dikirim', 'Kurir dalam perjalanan'],
    'delivered'  => ['Selesai', 'Pesanan diterima pelanggan'],
];
$step_keys = array_keys($timeline);
$is_cancelled = $order['status'] === 'cancelled';
$cur = $is_cancelled ? null : array_search($order['status'], $step_keys, true);

$can_verify = in_array($method, ['transfer', 'ewallet'], true)
    && $order['payment_status'] !== 'paid'
    && $order['payment_status'] !== 'refunded'
    && !empty($order['payment_proof'])
    && !in_array($order['status'], ['cancelled', 'delivered'], true);
?>

<a href="/geprek-geh/admin/orders" class="back-link">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
    Kembali ke Pesanan
</a>
<div class="breadcrumb">
    <a href="/geprek-geh/admin">Dashboard</a>
    <span>/</span>
    <a href="/geprek-geh/admin/orders">Pesanan</a>
    <span>/</span>
    <span><?= e($order['invoice_no']) ?></span>
</div>

<div class="page-header page-header--wrap">
    <div>
        <h1 class="order-heading">
            <span id="oc-invoice"><?= e($order['invoice_no']) ?></span>
            <button type="button" class="btn-copy" data-copy="#oc-invoice" aria-label="Salin no. invoice" title="Salin no. invoice">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
            </button>
        </h1>
        <p class="page-sub">Dibuat <?= e($created) ?> &middot; <?= e($method_label) ?></p>
    </div>
    <div class="order-head-actions">
        <?php if ($wa): ?>
            <a href="<?= e($wa) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-ghost">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21l1.65-4.7A8 8 0 1 1 7.7 19.3z"/><path d="M9 10a.5.5 0 0 0 1 0V9.5a.5.5 0 0 0-1 0zm0 0a5 5 0 0 0 5 5m0 0h.5a.5.5 0 0 0 0-1H14a.5.5 0 0 0 0 1z"/></svg>
                WhatsApp
            </a>
        <?php endif; ?>
        <button type="button" class="btn btn-sm btn-outline" data-copy="#oc-summary">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
            Salin Rincian
        </button>
        <a href="/geprek-geh/admin/orders/<?= $order['id'] ?>/print" target="_blank" rel="noopener" class="btn btn-sm btn-primary">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>
            Cetak
        </a>
    </div>
</div>

<pre id="oc-summary" hidden><?= e($order_summary) ?></pre>

<div class="order-badge-row">
    <span class="badge <?= $badge_class ?>"><?= $status_label ?></span>
    <span class="badge <?= $payment_badge ?>">Pembayaran: <?= $payment_status_label ?></span>
    <?php if (!empty($order['tracking_no'])): ?>
        <span class="track-chip">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
            Resi: <?= e($order['tracking_no']) ?>
        </span>
    <?php endif; ?>
</div>

<?php if ($is_cancelled): ?>
    <div class="cancel-banner">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
        <div>
            <strong>Pesanan ini dibatalkan.</strong>
            Alasan: &ldquo;<?= e($order['cancel_reason'] ?: 'Tidak tercatat') ?>&rdquo;
            <?php if ($order['payment_status'] === 'refunded'): ?>
                — pembayaran berstatus <b>Refund</b>: <em><?= rupiah($grand) ?></em> dikembalikan ke pelanggan.
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <div class="order-timeline">
        <?php $tl_i = 0;
        foreach ($timeline as $step):
            $done = $cur !== null && $tl_i <= $cur;
            $is_cur = $tl_i === $cur;
        ?>
            <div class="tl-step <?= $done ? 'is-done' : '' ?> <?= $is_cur ? 'is-current' : '' ?>">
                <span class="tl-dot"><?= $done ? '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>' : ($tl_i + 1) ?></span>
                <div class="tl-body">
                    <span class="tl-label"><?= e($step[0]) ?></span>
                    <span class="tl-desc"><?= e($step[1]) ?></span>
                </div>
                <?php if ($tl_i < count($timeline) - 1): ?><span class="tl-connector <?= $done && !$is_cur ? 'is-fill' : '' ?>"></span><?php endif; ?>
            </div>
        <?php $tl_i++;
        endforeach; ?>
    </div>
<?php endif; ?>

<div class="admin-grid-2 order-detail">
    <div class="order-main-col">

        <div class="card order-card">
            <header class="order-card-head">
                <h3>Pelanggan</h3>
            </header>
            <div class="ocust-ident">
                <span class="ocust-avatar ocust-avatar--lg"><?= e(mb_strtoupper(mb_substr($order['customer_name'], 0, 1))) ?></span>
                <div class="ocust-ident-meta">
                    <strong><?= e($order['customer_name']) ?></strong>
                    <span><?= $order['customer_email'] ? '<a href="mailto:' . e($order['customer_email']) . '">' . e($order['customer_email']) . '</a>' : 'Tanpa email' ?></span>
                </div>
            </div>
            <div class="order-meta-grid">
                <div class="meta-cell">
                    <span class="meta-label">Telepon</span>
                    <span class="meta-value"><?= !empty($order['customer_phone']) ? e($order['customer_phone']) : '-' ?></span>
                </div>
                <div class="meta-cell">
                    <span class="meta-label">Metode Bayar</span>
                    <span class="meta-value"><?= e($method_label) ?></span>
                </div>
                <div class="meta-cell meta-cell--full">
                    <span class="meta-label">Alamat Pengiriman</span>
                    <span class="meta-value"><?= $order['shipping_address'] ? nl2br(e($order['shipping_address'])) : '-' ?></span>
                </div>
                <?php if (!empty($order['notes'])): ?>
                <div class="meta-cell meta-cell--full">
                    <span class="meta-label">Catatan Pelanggan</span>
                    <span class="meta-value"><?= e($order['notes']) ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($order['promo_code'])): ?>
                <div class="meta-cell meta-cell--full">
                    <span class="meta-label">Kode Promo</span>
                    <span class="meta-value"><span class="promo-chip"><?= e($order['promo_code']) ?></span></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card order-card">
            <header class="order-card-head">
                <h3>Item Pesanan <?php if ($items): ?><span class="order-qty-chip"><?= $total_qty ?> item</span><?php endif; ?></h3>
            </header>
            <?php if (empty($items)): ?>
                <p class="text-muted">Item tidak tersedia (produk terkait sudah dihapus).</p>
            <?php else: ?>
            <div class="oitems">
                <?php foreach ($items as $item):
                    $sub = (int) $item['price'] * (int) $item['quantity'];
                ?>
                <div class="oitem">
                    <div class="oitem-thumb">
                        <?php if (!empty($item['image'])): ?>
                            <img src="/geprek-geh/assets/uploads/products/<?= e($item['image']) ?>" alt="<?= e($item['name']) ?>" loading="lazy">
                        <?php else: ?>
                            <span class="oitem-ph"><?= e(mb_strtoupper(mb_substr($item['name'], 0, 1))) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="oitem-main">
                        <span class="oitem-name"><?= e($item['name']) ?></span>
                        <span class="oitem-meta"><?= rupiah($item['price']) ?> &times; <?= (int) $item['quantity'] ?></span>
                    </div>
                    <span class="oitem-sub"><?= rupiah($sub) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="order-totals">
                <div class="order-line"><span>Subtotal</span><span><?= rupiah($order['total']) ?></span></div>
                <?php if ((int) $order['discount'] > 0): ?>
                    <div class="order-line"><span>Diskon<?= $order['promo_code'] ? ' <em>(' . e($order['promo_code']) . ')</em>' : '' ?></span><span>&minus; <?= rupiah($order['discount']) ?></span></div>
                <?php endif; ?>
                <div class="order-line"><span>Ongkos Kirim</span><span><?= rupiah($order['shipping_cost']) ?></span></div>
                <div class="order-line"><span>Pajak</span><span><?= rupiah($order['tax']) ?></span></div>
                <div class="order-line total"><span>Grand Total</span><span id="oc-grand"><?= rupiah($grand) ?></span></div>
            </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($logs)): ?>
        <div class="card order-card">
            <header class="order-card-head">
                <h3>Riwayat Pesanan</h3>
            </header>
            <div class="log-list">
                <?php foreach ($logs as $log): ?>
                <div class="log-item">
                    <span class="log-dot log-dot--<?= $log['actor'] ?>"></span>
                    <span class="log-msg"><?= e($log['message']) ?></span>
                    <span class="log-time"><?= date('d M Y, H:i', strtotime($log['created_at'])) ?> &middot; <?= $log['actor'] === 'admin' ? 'Admin' : 'Pelanggan' ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <div class="order-side-col">

        <div class="card order-card">
            <header class="order-card-head">
                <h3>Pembayaran &amp; Tagihan</h3>
            </header>
            <div class="pay-amount-banner">
                <div>
                    <span class="pay-amount-label"><?= $order['payment_status'] === 'paid' ? 'Dibayar lunas' : 'Total tagihan' ?></span>
                    <span class="pay-amount-value"><?= rupiah($grand) ?></span>
                </div>
                <span class="badge <?= $payment_badge ?>"><?= $payment_status_label ?></span>
            </div>

            <div class="order-meta-grid">
                <?php if (!in_array($method, ['cod'], true)): ?>
                <div class="meta-cell meta-cell--full">
                    <span class="meta-label">Rekening Tujuan</span>
                    <span class="meta-value"><?= e($bank['name']) ?> &bull; <?= e($bank['number']) ?> a.n. <?= e($bank['holder']) ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($order['payment_bank'])): ?>
                <div class="meta-cell">
                    <span class="meta-label">Dibayar dari</span>
                    <span class="meta-value"><?= e($order['payment_bank']) ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($order['payment_account_name'])): ?>
                <div class="meta-cell">
                    <span class="meta-label">Atas Nama</span>
                    <span class="meta-value"><?= e($order['payment_account_name']) ?><?= !empty($order['payment_account_no']) ? ' &bull; ' . e($order['payment_account_no']) : '' ?></span>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($proof_exists): ?>
            <div class="proof-box">
                <span class="meta-label">Bukti Pembayaran</span>
                <a href="/geprek-geh/assets/uploads/payments/<?= e($order['payment_proof']) ?>" target="_blank" rel="noopener" class="proof-preview">
                    <img src="/geprek-geh/assets/uploads/payments/<?= e($order['payment_proof']) ?>" alt="Bukti pembayaran" loading="lazy">
                    <span class="proof-zoom">Perbesar bukti</span>
                </a>
            </div>
            <?php elseif (!empty($order['payment_proof'])): ?>
                <p class="proof-missing">File bukti tidak ditemukan di server (<code><?= e($order['payment_proof']) ?></code>). Verifikasi manual via WhatsApp.</p>
            <?php elseif (!$is_cod && $order['payment_status'] === 'unpaid'): ?>
                <p class="proof-missing">Belum ada bukti pembayaran dari pelanggan.</p>
            <?php endif; ?>

            <?php if ($can_verify): ?>
            <form method="POST" action="/geprek-geh/admin/orders/<?= $order['id'] ?>/verify-payment" class="order-action" data-confirm="Tandai pembayaran pesanan ini LUNAS? Status akan otomatis lanjut ke &quot;Diproses&quot; bila masih menunggu.">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-success">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                    Tandai Lunas &amp; Proses
                </button>
            </form>
            <?php endif; ?>
        </div>

        <div class="card order-card">
            <header class="order-card-head">
                <h3>Perbarui Status</h3>
            </header>
            <?php if (empty($transitions)): ?>
                <p class="status-terminal">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                    Status <strong><?= $status_label ?></strong> adalah tahap akhir — tidak ada transisi lanjutan.
                </p>
            <?php else:
                $rank = ['pending' => 0, 'processing' => 1, 'shipped' => 2, 'delivered' => 3];
                $cur_rank = $rank[$order['status']] ?? null;
                $steps = [
                    'pending'    => 'Menunggu',
                    'processing' => 'Diproses',
                    'shipped'    => 'Dikirim',
                    'delivered'  => 'Selesai',
                ];
                $step_i = 0;
            ?>
            <p class="status-now">Saat ini <strong><?= $status_label ?></strong> — pilih langkah berikutnya. Langkah yang sudah terlewati <span class="text-muted">abu-abu &amp; nonaktif</span>.</p>
            <form method="POST" action="/geprek-geh/admin/orders/<?= $order['id'] ?>/status" class="status-stepper-form" data-status-stepper>
                <?= csrf_field() ?>
                <div class="status-stepper">
                    <?php foreach ($steps as $key => $label):
                        $cls = 'status-step';
                        $attrs = '';
                        if ($cur_rank === null || $step_i < $cur_rank) {
                            $cls .= ' is-past';
                            $attrs .= ' disabled';
                        } elseif ($step_i === $cur_rank) {
                            $cls .= ' is-current';
                            $attrs .= ' disabled';
                        } elseif (!in_array($key, $transitions, true)) {
                            $cls .= ' is-locked';
                            $attrs .= ' disabled title="Belum dapat dipilih — selesaikan langkah sebelumnya dulu"';
                        } elseif ($key === 'shipped') {
                            $cls .= ' is-next has-extra';
                            $attrs .= ' type="button" data-open-extra="shipped" data-label="Dikirim"';
                        } else {
                            $cls .= ' is-next';
                            $attrs .= ' type="button" data-step="' . $key . '" data-label="' . e($label) . '"';
                        }
                    ?>
                        <button <?= $attrs ?> class="<?= $cls ?>">
                            <span class="status-step-no"><?= $step_i + 1 ?></span>
                            <span class="status-step-label"><?= e($label) ?></span>
                        </button>
                    <?php $step_i++; endforeach; ?>
                </div>

                <?php if (in_array('cancelled', $transitions, true)): ?>
                <button type="button" class="btn btn-outline-danger btn-block status-cancel-btn" data-open-extra="cancelled" data-label="Batalkan Pesanan">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4M12 17h.01"/><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg>
                    Batalkan Pesanan
                </button>
                <?php endif; ?>

                <div class="status-panel" data-status-panel hidden>
                    <span class="status-panel-tag">Langkah berikutnya: <strong data-panel-label></strong></span>
                    <input type="hidden" name="status" data-panel-status>
                    <div class="status-extra-field" data-panel-extra="shipped" hidden>
                        <span class="status-extra-label">Nomor Resi <span class="text-muted">(opsional)</span></span>
                        <input type="text" name="tracking_no" class="input" placeholder="cth. JNE-FFC-000123456" maxlength="80">
                    </div>
                    <div class="status-extra-field" data-panel-extra="cancelled" hidden>
                        <span class="status-extra-label">Alasan Pembatalan <span class="text-muted">* wajib</span></span>
                        <textarea name="cancel_reason" class="input" rows="2" placeholder="Alasan ini terlihat oleh pelanggan" maxlength="255" required></textarea>
                    </div>
                    <div class="status-panel-actions">
                        <button type="submit" class="btn btn-primary">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                            Simpan Status
                        </button>
                        <button type="button" class="btn btn-ghost" data-panel-cancel>Batal</button>
                    </div>
                </div>
            </form>
            <?php endif; ?>
        </div>

    </div>
</div>