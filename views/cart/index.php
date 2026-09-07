<?php $page_title = 'Keranjang'; ?>

<section class="cart-hero">
    <div class="cart-hero-inner">
        <span class="cart-eyebrow">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2 1.61L23 6H6"/></svg>
            Keranjang Belanja
        </span>
        <div class="cart-hero-title-row">
            <h1 class="cart-hero-title">Keranjangmu</h1>
            <?php if (!empty($items)): ?>
                <span class="cart-hero-count"><?= count($items) ?> item</span>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php if (empty($items)): ?>
    <div class="empty-state">
        <span class="ghost">
            <svg width="96" height="96" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="0.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1.5"/><circle cx="19" cy="21" r="1.5"/><path d="M2.5 3h2l2.6 12.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L22.5 7H6"/></svg>
        </span>
        <h3>Keranjangmu masih kosong — untuk saat ini.</h3>
        <p>Yuk isi dengan geprek renyah dan sambal level sesuai seleramu. Kami masak per pesanan, jadi selalu hangat sampai depan pintu.</p>
        <a href="/geprek-geh/products" class="btn btn-primary">Lihat Menu
            <span class="btn-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </span>
        </a>

        <div class="empty-tips">
            <div class="tip-pill"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                <span>Tingkat pedas bisa dikustom</span></div>
            <div class="tip-pill"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2v6a6 6 0 0 0 12 0V2M6 2h4M18 2h-4"/><path d="M6 2v6L4 6M18 2v6l2-2"/></svg>
                <span>Diantar hangam sampai pintu</span></div>
            <div class="tip-pill"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l8 4v6c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6z"/><path d="M9 14l2 2 4-4"/></svg>
                <span>Fresh, higienis, aman</span></div>
        </div>
    </div>
<?php else: ?>
<div class="cart-layout">
    <div class="cart-items">
        <div class="cart-list-head">
            <span class="cart-list-title">Item Pesanan</span>
            <form method="POST" action="/geprek-geh/cart/clear" class="cart-clear-form" data-confirm="Semua item di keranjang akan dihapus. Lanjutkan?">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm btn-ghost btn-dangerghost">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                    Kosongkan
                </button>
            </form>
        </div>
        <?php foreach ($items as $i => $item):
            $line_total = $item['price'] * $item['quantity'];
            $out = $item['stock'] <= 0;
        ?>
        <div class="cart-item" data-reveal>
            <a class="cart-item-img" href="/geprek-geh/products/<?= e($item['slug']) ?>">
                <?php if ($item['image']): ?>
                    <img src="/geprek-geh/assets/uploads/products/<?= e($item['image']) ?>" alt="<?= e($item['name']) ?>" loading="lazy" decoding="async">
                <?php else: ?>
                    <span class="cart-item-placeholder"><?= product_art($item['name'], $item['category_name'] ?? '', '', 160) ?></span>
                <?php endif; ?>
            </a>

            <div class="cart-item-body">
                <span class="cart-item-cat"><?= e($item['category_name'] ?? '') ?></span>
                <h3 class="cart-item-name">
                    <a href="/geprek-geh/products/<?= e($item['slug']) ?>"><?= e($item['name']) ?></a>
                </h3>
                <div class="cart-item-meta">
                    <span class="cart-item-price"><?= rupiah($item['price']) ?> / porsi</span>
                    <span class="cart-item-stock <?= $out ? 'is-out' : ($item['stock'] <= 5 ? 'is-low' : 'is-in') ?>">
                        <?= $out ? 'Stok habis' : ($item['stock'] <= 5 ? "Sisa {$item['stock']}" : 'Stok tersedia') ?>
                    </span>
                </div>

                <div class="cart-item-actions">
                    <form method="POST" action="/geprek-geh/cart/update" class="cart-qty-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="cart_id" value="<?= $item['id'] ?>">
                        <div class="cart-stepper" <?= $out || $item['stock'] === 1 ? 'data-disabled' : '' ?>>
                            <button type="button" class="cart-step-btn" data-qty-step="-1" aria-label="Kurangi">&minus;</button>
                            <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" max="<?= max(1, $item['stock']) ?>" class="cart-step-input" <?= $out ? 'disabled' : '' ?>>
                            <button type="button" class="cart-step-btn" data-qty-step="1" aria-label="Tambah" <?= $out || $item['quantity'] >= $item['stock'] ? 'disabled' : '' ?>>+</button>
                        </div>
                    </form>

                    <div class="cart-item-line-total">
                        <small>Subtotal</small>
                        <strong><?= rupiah($line_total) ?></strong>
                    </div>

                    <form method="POST" action="/geprek-geh/cart/remove" class="cart-remove-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="cart_id" value="<?= $item['id'] ?>">
                        <button type="submit" class="cart-remove-btn" title="Hapus item">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6M10 11v6M14 11v6"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="cart-actions-row">
            <a href="/geprek-geh/products" class="btn btn-ghost">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                Lanjut Belanja
            </a>
        </div>
    </div>

    <aside class="cart-summary">
        <div class="cart-summary-card">
            <h4 class="cart-summary-title">Ringkasan Pesanan</h4>
            <div class="cart-summary-lines">
                <div class="cart-sum-line">
                    <span>Subtotal (<?= count($items) ?> item)</span>
                    <span><?= rupiah($subtotal) ?></span>
                </div>
                <div class="cart-sum-line">
                    <span>Ongkir</span>
                    <span>Rp<?= number_format($shipping, 0, ',', '.') ?></span>
                </div>
                <div class="cart-sum-line">
                    <span>Pajak (11%)</span>
                    <span><?= rupiah($tax) ?></span>
                </div>

                <?php if ($discount > 0 && $promo): ?>
                <div class="cart-sum-line cart-discount-line">
                    <span>Diskon (<?= e($promo_label) ?>)</span>
                    <span class="is-discount">−<?= rupiah($discount) ?></span>
                </div>
                <?php endif; ?>
            </div>
            <div class="cart-sum-total">
                <span>Total</span>
                <strong><?= rupiah($grand_total) ?></strong>
            </div>

            <?php if (Auth::check()): ?>
                <a href="/geprek-geh/checkout" class="btn btn-primary btn-lg btn-block">
                    Checkout Sekarang
                    <span class="btn-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </span>
                </a>
            <?php else: ?>
                <a href="/geprek-geh/auth/login" class="btn btn-primary btn-lg btn-block">Masuk untuk Checkout</a>
            <?php endif; ?>

            <p class="cart-summary-note">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                Ongkir &amp; pajak dihitung otomatis saat checkout.
            </p>
        </div>
    </aside>
</div>
<?php endif; ?>