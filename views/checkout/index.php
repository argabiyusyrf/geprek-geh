<?php
$page_title = 'Checkout';
$bank = $payment_details['bank'] ?? ['name' => '-', 'number' => '-', 'holder' => '-'];
$ewallet = $payment_details['ewallet'] ?? ['name' => 'E-Wallet', 'number' => '-', 'holder' => '-'];
?>

<div class="page-top">
    <header class="page-hero checkout-hero">
        <p class="eyebrow">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
            Tampan Pesanan
        </p>
        <h1>Checkout</h1>
        <p class="sub">Selesaikan pengiriman &amp; pembayaran. Pesananmu langsung diteruskan ke dapur begitu tombol diproses.</p>

        <a href="/geprek-geh/cart" class="back-to-cart back-to-cart--top">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Kembali ke keranjang
        </a>

        <div class="checkout-steps" aria-label="Langkah">
            <div class="checkout-step is-active">
                <span class="step-dot">1</span>
                <span class="step-label">Keranjang</span>
            </div>
            <span class="step-connector"></span>
            <div class="checkout-step is-current">
                <span class="step-dot">2</span>
                <span class="step-label">Detail &amp; Bayar</span>
            </div>
            <span class="step-connector"></span>
            <div class="checkout-step">
                <span class="step-dot">3</span>
                <span class="step-label">Selesai</span>
            </div>
        </div>
    </header>
</div>

<form method="POST" action="/geprek-geh/checkout" class="checkout-form">
    <?= csrf_field() ?>

    <div class="checkout-grid">
        <div class="checkout-left">
            <!-- 1. Alamat pengiriman -->
            <section class="card checkout-card" data-reveal>
                <div class="card-body">
                    <div class="checkout-card-head">
                        <span class="checkout-card-num">01</span>
                        <div>
                            <h3 class="checkout-card-title">Alamat Pengiriman</h3>
                            <p class="checkout-card-sub">Pastikan data benar agar kurir mudah menemukanmu.</p>
                        </div>
                    </div>

                    <?php if (!empty($saved_addresses)): ?>
                    <div class="saved-addresses" data-saved-addresses>
                        <p class="saved-addresses-title">Alamat tersimpan</p>
                        <?php foreach ($saved_addresses as $i => $sa): ?>
                            <button type="button" class="saved-address <?= (int) $sa['is_default'] === 1 ? 'is-active' : '' ?>"
                                    data-saved-address
                                    data-recipient="<?= e($sa['recipient_name']) ?>"
                                    data-phone="<?= e($sa['phone']) ?>"
                                    data-address="<?= e($sa['address']) ?>">
                                <span class="sa-label">
                                    <?= e($sa['label'] ?: 'Alamat') ?>
                                    <?php if ((int) $sa['is_default'] === 1): ?><span class="address-badge">Utama</span><?php endif; ?>
                                </span>
                                <span class="sa-body">
                                    <span class="sa-name"><?= e($sa['recipient_name']) ?></span>
                                    <span class="sa-addr"><?= e($sa['address']) ?></span>
                                    <?php $region = array_filter([$sa['village'], $sa['district'], $sa['city'], $sa['province']], fn($v) => !empty($v)); ?>
                                    <?php if ($region): ?><span class="sa-region"><?= e(implode(', ', $region)) ?></span><?php endif; ?>
                                </span>
                            </button>
                        <?php endforeach; ?>
                        <button type="button" class="saved-address is-manual <?= empty($field_errors) && empty($saved_manual) ? '' : 'is-active' ?>"
                                data-saved-manual>
                            <span class="sa-label">Tulis manual</span>
                            <span class="sa-body"><span class="sa-name">Isi alamat secara manual</span></span>
                        </button>
                    </div>
                    <?php endif; ?>

                    <div class="form-grid">
                        <div class="form-group">
                            <label>Nama Penerima *</label>
                            <input type="text" name="recipient_name" class="input <?= !empty($field_errors['recipient_name']) ? 'is-invalid' : '' ?>" value="<?= e($recipient_name ?? $user['name']) ?>" placeholder="Nama lengkap" required>
                            <?php if (!empty($field_errors['recipient_name'])): ?><span class="field-error"><?= e($field_errors['recipient_name']) ?></span><?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label>No. Telepon *</label>
                            <input type="tel" name="phone" class="input <?= !empty($field_errors['phone']) ? 'is-invalid' : '' ?>" value="<?= e($phone ?? $user['phone'] ?? '') ?>" placeholder="08xxxxxxxxxx" inputmode="numeric" required>
                            <?php if (!empty($field_errors['phone'])): ?><span class="field-error"><?= e($field_errors['phone']) ?></span><?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Alamat Lengkap *</label>
                        <textarea name="address" class="input <?= !empty($field_errors['address']) ? 'is-invalid' : '' ?>" rows="3" placeholder="Jalan, No, RT/RW, Kelurahan, Kecamatan, Kota, Kode Pos" required><?= e($address ?? $user['address'] ?? '') ?></textarea>
                        <?php if (!empty($field_errors['address'])): ?><span class="field-error"><?= e($field_errors['address']) ?></span><?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Catatan untuk dapur <span class="label-optional">(opsional)</span></label>
                        <textarea name="notes" class="input" rows="2" placeholder="Contoh: level pedas 2, tanpa timun, mintakan sambal terpisah..."><?= e($notes ?? '') ?></textarea>
                    </div>
                </div>
            </section>

            <!-- 2. Metode pembayaran -->
            <section class="card checkout-card" data-reveal>
                <div class="card-body">
                    <div class="checkout-card-head">
                        <span class="checkout-card-num">02</span>
                        <div>
                            <h3 class="checkout-card-title">Metode Pembayaran</h3>
                            <p class="checkout-card-sub">Pilih cara bayar. Transfer &amp; e-wallet diverifikasi admin.</p>
                        </div>
                    </div>

                    <div class="payment-options">
                        <?php foreach ($payment_options as $val => $opt): ?>
                        <label class="radio-card">
                            <input type="radio" name="payment_method" value="<?= $val ?>" <?= ($payment_method ?? 'transfer') === $val ? 'checked' : '' ?>>
                            <span class="radio-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                    <?php if ($opt['icon'] === 'bank'): ?><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20M6 15h4"/>
                                    <?php elseif ($opt['icon'] === 'cash'): ?><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="3"/><path d="M6 12h.01M18 12h.01"/>
                                    <?php else: ?><rect x="3" y="7" width="18" height="12" rx="3"/><path d="M7 10h8M15 14h2"/>
                                    <?php endif; ?>
                                </svg>
                            </span>
                            <span class="radio-body">
                                <span class="radio-title"><?= e($opt['label']) ?></span>
                                <span class="radio-desc"><?= e($opt['desc']) ?></span>
                            </span>
                            <span class="radio-check"></span>
                        </label>
                        <?php endforeach; ?>
                    </div>

                    <div class="pay-info-wrap" data-pay-info-wrap>
                        <div class="pay-info" data-pay-info="transfer">
                            <span class="pay-info-label">Transfer ke rekening kami:</span>
                            <span class="pay-info-detail"><strong><?= e($bank['name']) ?></strong> • <?= e($bank['number']) ?> a.n. <?= e($bank['holder']) ?></span>
                        </div>
                        <div class="pay-info" data-pay-info="ewallet">
                            <span class="pay-info-label">Bayar via e-wallet:</span>
                            <span class="pay-info-detail"><strong><?= e($ewallet['name']) ?></strong> • <?= e($ewallet['number']) ?> a.n. <?= e($ewallet['holder']) ?></span>
                        </div>
                        <div class="pay-info" data-pay-info="cod">
                            <span class="pay-info-label">Bayar di tempat (COD):</span>
                            <span class="pay-info-detail">Siapkan uang tunai tepat sesuai total pesanan saat pesanan tiba.</span>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <div class="checkout-right">
            <!-- Ringkasan pesanan -->
            <div class="order-summary sticky" data-reveal>
                <div class="card">
                    <div class="card-body">
                        <h3 class="order-summary-title">
                            Ringkasan Pesanan
                            <span class="order-summary-count"><?= $total_qty ?> item</span>
                        </h3>

                        <div class="order-items">
                            <?php foreach ($items as $item): ?>
                            <div class="order-item">
                                <div class="order-item-media">
                                    <div class="order-item-thumb">
                                        <?php if ($item['image']): ?>
                                            <img src="/geprek-geh/assets/uploads/products/<?= e($item['image']) ?>" alt="<?= e($item['name']) ?>" loading="lazy">
                                        <?php else: ?>
                                            <span class="order-item-placeholder"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><path d="M15.5 11.5a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0z"/><path d="M11 7V3M13.5 4.5L8.5 6.5M15 7.5l-6 1.5"/></svg></span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="order-item-qty"><?= $item['quantity'] ?></span>
                                </div>
                                <div class="order-item-info">
                                    <span class="order-item-name"><?= e($item['name']) ?></span>
                                    <span class="order-item-price"><?= rupiah($item['price']) ?> / porsi</span>
                                </div>
                                <span class="order-item-total"><?= rupiah($item['price'] * $item['quantity']) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="order-totals">
                            <div class="order-line">
                                <span>Subtotal <em>(<?= count($items) ?> item)</em></span>
                                <span><?= rupiah($subtotal) ?></span>
                            </div>
                            <div class="order-line">
                                <span>Pajak <em>11%</em></span>
                                <span><?= rupiah($tax) ?></span>
                            </div>
                            <div class="order-line">
                                <span>Ongkir</span>
                                <span><?= rupiah($shipping) ?></span>
                            </div>
                            <div class="order-line total">
                                <span>Grand Total</span>
                                <span><?= rupiah($grand_total) ?></span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg btn-block checkout-submit">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 7L10 17l-5-5"/></svg>
                            <span class="checkout-submit-label">Buat Pesanan</span>
                        </button>

                        <p class="checkout-note">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            Data &amp; transaksimu dilindungi dengan aman.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>