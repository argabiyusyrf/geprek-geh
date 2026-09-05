<?php
$page_title = 'Detail Pesanan';
[$status_label, $badge_class] = format_status($order['status']);
[$payment_status_label, $payment_badge] = format_payment_status($order['payment_status']);
$created = date('d M Y, H:i', strtotime($order['created_at']));
$bank_details = $payment_details['bank'] ?? ['name' => '-', 'number' => '-', 'holder' => '-'];
$ewallet_details = $payment_details['ewallet'] ?? ['name' => 'E-Wallet', 'number' => '-', 'holder' => '-'];
$wa_number = $contacts['whatsapp'] ?? '';
?>

<div class="page-top">
    <div class="breadcrumb">
        <a href="/geprek-geh/orders">Pesanan Saya</a>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg>
        <span><?= e($order['invoice_no']) ?></span>
    </div>

    <header class="page-hero order-hero">
        <div class="order-hero-badge">
            <?php if ($order['status'] === 'delivered'): ?>
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
            <?php elseif ($order['status'] === 'cancelled'): ?>
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
            <?php else: ?>
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2z"/><path d="M8 8h8M8 12h8M8 16h5"/></svg>
            <?php endif; ?>
        </div>
        <p class="eyebrow"><?= $order['status'] === 'cancelled' ? 'Pesanan Dibatalkan' : 'Terima kasih, pesananmu diterima ya!' ?></p>
        <h1><?= e($order['invoice_no']) ?></h1>
        <p class="sub">
            <?php if ($order['status'] === 'cancelled'): ?>
                <?= $order['cancel_reason'] ? e($order['cancel_reason']) . '.' : 'Pesanan ini telah dibatalkan.' ?>
                <?php if ($order['payment_status'] === 'refunded'): ?> Pembayaran yang sudah lunas akan di-refund.<?php endif; ?>
            <?php else: ?>
                Pesanan dibuat pada <?= $created ?>. Kami akan segera memproses pesananmu.
            <?php endif; ?>
        </p>
        <span class="badge order-hero-pill <?= $badge_class ?>"><?= $status_label ?></span>
        <?php if ($order['status'] !== 'cancelled'): ?>
        <span class="badge order-hero-pill <?= $payment_badge ?>"><?= $payment_status_label ?></span>
        <?php endif; ?>
    </header>
</div>

<?php if ($order['status'] !== 'cancelled'): ?>
<div class="order-timeline" data-reveal>
    <?php foreach ($timeline as $i => $step):
        $done = $i <= $current_rank;
        $is_cur = $i === $current_rank;
    ?>
        <div class="tl-step <?= $done ? 'is-done' : '' ?> <?= $is_cur ? 'is-current' : '' ?>">
            <span class="tl-dot"><?= $done ? '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>' : ($i + 1) ?></span>
            <div class="tl-body">
                <span class="tl-label"><?= e($step['label']) ?></span>
                <span class="tl-desc"><?= e($step['desc']) ?></span>
            </div>
            <?php if ($i < count($timeline) - 1): ?><span class="tl-connector <?= $done && !$is_cur ? 'is-fill' : '' ?>"></span><?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="order-detail-layout" data-reveal>
    <div class="order-detail-main">
        <!-- Items -->
        <section class="card order-card">
            <div class="card-body">
                <div class="order-card-title">
                    <h3>Item Pesanan <span class="order-qty-chip"><?= $total_qty ?> item</span></h3>
                </div>
                <div class="order-items-list">
                    <?php foreach ($items as $item): ?>
                    <div class="order-item">
                        <div class="order-item-thumb">
                            <?php if ($item['image']): ?>
                                <img src="/geprek-geh/assets/uploads/products/<?= e($item['image']) ?>" alt="<?= e($item['name']) ?>" loading="lazy">
                            <?php else: ?>
                                <span class="order-item-placeholder"><?= product_art($item['name'], $item['category_name'] ?? '', '', 90) ?></span>
                            <?php endif; ?>
                            <span class="order-item-qty"><?= $item['quantity'] ?></span>
                        </div>
                        <div class="order-item-info">
                            <a class="order-item-name" href="/geprek-geh/products/<?= e($item['slug']) ?>"><?= e($item['name']) ?></a>
                            <span class="order-item-price"><?= rupiah($item['price']) ?> / porsi</span>
                        </div>
                        <span class="order-item-total"><?= rupiah($item['price'] * $item['quantity']) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if ($order['notes']): ?>
                <div class="order-notes">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    <div><strong>Catatan dapur:</strong> <?= e($order['notes']) ?></div>
                </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Details -->
        <section class="card order-card">
            <div class="card-body">
                <h3>Detail Pengiriman</h3>
                <div class="order-meta-grid">
                    <div class="meta-cell">
                        <span class="meta-label">Metode Pembayaran</span>
                        <span class="meta-value"><?= e($payment_label) ?></span>
                    </div>
                    <div class="meta-cell">
                        <span class="meta-label">Status Pembayaran</span>
                        <span class="meta-value">
                            <?php if ($order['payment_method'] === 'cod'): ?>
                                <span class="payment-state"><?= $order['status'] === 'delivered' ? 'Lunas saat antar' : 'Bayar saat tiba' ?></span>
                            <?php else: ?>
                                <span class="badge <?= $payment_badge ?>"><?= $payment_status_label ?></span>
                                <?php if ($order['payment_proof'] && $order['payment_status'] !== 'paid'): ?> <span class="payment-state">(bukti terkirim)</span><?php endif; ?>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="meta-cell">
                        <span class="meta-label">Alamat Pengiriman</span>
                        <span class="meta-value"><?= e($order['shipping_address']) ?></span>
                    </div>
                    <div class="meta-cell">
                        <span class="meta-label">Waktu Dibuat</span>
                        <span class="meta-value"><?= $created ?></span>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <aside class="order-detail-side">
        <!-- Totals -->
        <div class="card order-summary-card">
            <div class="card-body">
                <h3>Ringkasan Pembayaran</h3>
                <div class="order-totals">
                    <div class="order-line"><span>Subtotal (<?= $total_qty ?> item)</span><span><?= rupiah($order['total']) ?></span></div>
                    <div class="order-line"><span>Ongkir</span><span><?= rupiah($order['shipping_cost']) ?></span></div>
                    <div class="order-line"><span>Pajak</span><span><?= rupiah($order['tax']) ?></span></div>
                    <div class="order-line total"><span>Grand Total</span><span><?= rupiah($order['grand_total']) ?></span></div>
                </div>
            </div>
        </div>

        <?php $need_proof = in_array($order['payment_method'], ['transfer', 'ewallet'], true); ?>

        <?php if ($need_proof && $order['payment_status'] === 'unpaid' && in_array($order['status'], ['pending', 'processing'], true)): ?>
        <div class="card order-card">
            <div class="card-body pay-instructions">
                <h3>Instruksi Pembayaran</h3>
                <?php if ($order['payment_method'] === 'transfer'): ?>
                    <p class="pay-instructions-line">Transfer ke rekening kami:</p>
                    <p class="pay-instructions-detail"><strong><?= e($bank_details['name']) ?></strong> • <?= e($bank_details['number']) ?> a.n. <?= e($bank_details['holder']) ?></p>
                    <p class="pay-instructions-note">Konfirmasi dengan mengunggah bukti di bawah. Verifikasi manual oleh admin 1×24 jam.</p>
                <?php else: ?>
                    <p class="pay-instructions-line">Bayar via e-wallet:</p>
                    <p class="pay-instructions-detail"><strong><?= e($ewallet_details['name']) ?></strong> • <?= e($ewallet_details['number']) ?> a.n. <?= e($ewallet_details['holder']) ?></p>
                    <p class="pay-instructions-note">Kirim bukti transfer/kode bayar di bawah agar diperiksa admin.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($need_proof && $order['payment_status'] === 'unpaid' && $order['status'] === 'pending'): ?>
        <div class="card order-card">
            <div class="card-body proof-section">
                <h3><?= $order['payment_proof'] ? 'Bukti Bayar' : 'Upload Bukti Bayar' ?></h3>
                <?php if ($order['payment_proof']): ?>
                    <p class="proof-ok"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>Bukti pembayaran sudah diupload.</p>
                    <img src="/geprek-geh/assets/uploads/payments/<?= e($order['payment_proof']) ?>" alt="Bukti" class="proof-img">
                <?php else: ?>
                    <p class="proof-hint">Transfer ke rekening kami lalu unggah buktinya agar verifikasi lebih cepat.</p>
                <form method="POST" action="/geprek-geh/orders/<?= $order['id'] ?>/upload-proof" enctype="multipart/form-data" class="proof-form">
                    <?= csrf_field() ?>
                    <label class="proof-field" data-proof-field>
                        <input type="file" name="proof" accept="image/*" class="proof-input" data-proof-input required>

                        <span class="proof-empty" data-proof-empty>
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>
                            <span class="proof-empty-title">Pilih gambar bukti</span>
                            <span class="proof-empty-hint">PNG, JPG, WebP · maks. 2MB</span>
                        </span>

                        <span class="proof-preview" data-proof-preview style="display:none">
                            <img src="" alt="Pratinjau bukti" class="proof-preview-img" data-proof-img>
                            <span class="proof-preview-name" data-proof-name></span>
                        </span>
                    </label>

                    <div class="proof-actions" data-proof-actions style="display:none">
                        <span class="proof-btn" data-proof-replace tabindex="0">Ganti</span>
                        <span class="proof-btn proof-btn-danger" data-proof-clear tabindex="0">Batal</span>
                    </div>

                    <p class="proof-error" data-proof-error hidden></p>
                    <button type="submit" class="btn btn-primary btn-block" data-proof-submit disabled>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>
                        Upload Bukti
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <a class="order-help" href="<?= wa_link($wa_number) ?>" target="_blank" rel="noopener">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            <div>
                <strong>Butuh bantuan?</strong>
                <span><?= e($wa_number ? 'WhatsApp ' . $wa_number . '' : 'Hubungi kami') ?><?= $wa_number ? ' · ' : ' ' ?><?= e($contacts['hours'] ?? '') ?> — siap bantu seputar pesananmu.</span>
            </div>
        </a>
    </aside>
</div>

<?php if (!empty($logs)): ?>
<section class="card order-card order-logs" data-reveal>
    <div class="card-body">
        <h3>Riwayat Pesanan</h3>
        <div class="log-list">
            <?php foreach ($logs as $log): ?>
            <div class="log-item">
                <span class="log-dot log-dot--<?= $log['actor'] ?>"></span>
                <span class="log-msg"><?= e($log['message']) ?></span>
                <span class="log-time"><?= date('d M Y, H:i', strtotime($log['created_at'])) ?> · <?= $log['actor'] === 'admin' ? 'Admin' : 'Kamu' ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<div class="order-actions">
    <?php
        $can_cancel = in_array($order['status'], ['pending', 'processing'], true);
        $can_receive = $order['status'] === 'shipped';
        $is_cancelled = $order['status'] === 'cancelled';
        $is_done = in_array($order['status'], ['delivered', 'cancelled'], true);
    ?>
    <?php if ($can_cancel): ?>
        <form method="POST" action="/geprek-geh/orders/<?= $order['id'] ?>/cancel" data-confirm="Yakin ingin membatalkan pesanan ini?">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-ghost btn-dangerghost btn-block">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
                Batalkan Pesanan
            </button>
        </form>
    <?php endif; ?>

    <?php if (!empty($can_receive)): ?>
        <form method="POST" action="/geprek-geh/orders/<?= $order['id'] ?>/receive" data-confirm="Konfirmasi bahwa pesanan sudah sampai & siap dinikmati?">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-primary btn-block">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                Pesanan Selesai
            </button>
        </form>
    <?php elseif (!$is_done): ?>
        <button type="button" class="btn btn-primary btn-block" disabled title="Pesanan bisa ditandai selesai setelah statusnya Sedang Dikirim">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
            Pesanan Selesai
        </button>
    <?php endif; ?>

    <?php if ($order['status'] === 'delivered'): ?>
        <form method="POST" action="/geprek-geh/orders/<?= $order['id'] ?>/reorder">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline btn-block">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 2.64-6.36M3 3v6h6"/></svg>
                Beli Lagi
            </button>
        </form>
    <?php endif; ?>
</div>

<div class="order-more">
    <a href="/geprek-geh/products" class="btn btn-ghost">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        Lanjut Belanja
    </a>
    <a href="/geprek-geh/orders" class="btn btn-outline">Lihat Semua Pesanan</a>
</div>