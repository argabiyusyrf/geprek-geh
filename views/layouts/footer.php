</div>
</main>

<footer class="footer">
    <div class="footer-inner">
        <div class="footer-brand">
            <a href="/geprek-geh/" class="brand">
                <span class="brand-mark">G</span>
                <span class="brand-word">Geprek Geh</span>
            </a>
            <p>Geprek pedas nikmat, disajikan hangat dengan sambal level sesuai seleramu. Pesan online, kami antar ke pintu rumahmu.</p>
        </div>
        <div class="footer-col">
            <h4>Navigasi</h4>
            <a href="/geprek-geh/products">Semua Menu</a>
            <a href="/geprek-geh/cart">Keranjang</a>
            <a href="/geprek-geh/orders">Pesanan Saya</a>
            <?php if (Auth::admin()): ?><a href="/geprek-geh/admin">Admin Panel</a><?php endif; ?>
        </div>
        <div class="footer-col">
            <h4>Kontak</h4>
            <a href="tel:081234567890">+62 812-3456-7890</a>
            <a href="mailto:halo@geprekgeh.com">halo@geprekgeh.com</a>
            <a href="https://maps.google.com/?q=Jakarta" target="_blank" rel="noopener">Jl. Merdeka No. 10, Jakarta</a>
        </div>
    </div>
    <div class="footer-base">
        <span>© <?= date('Y') ?> Geprek Geh. Semua hak dilindungi.</span>
        <span>Dibuat dengan pedas di Indonesia</span>
    </div>
</footer>

<?php $cs = cart_summary(); ?>
<!-- ── Cart drawer global ───────────────────────────── -->
<div class="drawer" id="cart-drawer" aria-hidden="true">
    <div class="drawer-scrim" data-close-drawer></div>
    <aside class="drawer-panel" role="dialog" aria-modal="true" aria-label="Keranjang belanja">
        <header class="drawer-head">
            <div>
                <span class="drawer-eyebrow">Keranjang Anda</span>
                <h3 class="drawer-title">Pesananmu <em><?= $cs['count'] ? '(' . $cs['count'] . ')' : '' ?></em></h3>
            </div>
            <button class="drawer-close" type="button" data-close-drawer aria-label="Tutup">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
        </header>

        <div class="drawer-body">
            <?php if (empty($cs['items'])): ?>
                <div class="drawer-empty">
                    <span class="drawer-empty-icon">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1.5"/><circle cx="19" cy="21" r="1.5"/><path d="M2.5 3h2l2.6 12.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L22.5 7H6"/></svg>
                    </span>
                    <strong>Keranjang kosong</strong>
                    <p>Yuk pilih menu favoritmu &amp; mulai pesan.</p>
                    <a href="/geprek-geh/products" class="btn btn-primary btn-block" data-close-drawer>Lihat Menu</a>
                </div>
            <?php else: ?>
                <ul class="drawer-list">
                    <?php foreach ($cs['items'] as $d): $d_total = $d['price'] * $d['quantity']; ?>
                    <li class="drawer-item">
                        <a class="drawer-media" href="/geprek-geh/products/<?= e($d['slug']) ?>" data-close-drawer>
                            <?php if ($d['image']): ?>
                                <img src="/geprek-geh/assets/uploads/products/<?= e($d['image']) ?>" alt="<?= e($d['name']) ?>" loading="lazy">
                            <?php else: ?>
                                <?= product_art($d['name'], $d['category_name'], '', 200) ?>
                            <?php endif; ?>
                        </a>
                        <div class="drawer-info">
                            <span class="drawer-cat"><?= e($d['category_name']) ?></span>
                            <a class="drawer-name" href="/geprek-geh/products/<?= e($d['slug']) ?>" data-close-drawer><?= e($d['name']) ?></a>
                            <div class="drawer-meta">
                                <span class="drawer-price"><?= rupiah($d['price']) ?></span>
                                <span class="drawer-qty">&times;<?= $d['quantity'] ?></span>
                            </div>
                        </div>
                        <div class="drawer-side">
                            <strong><?= rupiah($d_total) ?></strong>
                            <form method="POST" action="/geprek-geh/cart/remove">
                                <?= csrf_field() ?>
                                <input type="hidden" name="cart_id" value="<?= $d['id'] ?>">
                                <button class="drawer-remove" type="submit" aria-label="Hapus <?= e($d['name']) ?>">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                                </button>
                            </form>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <?php if (!empty($cs['items'])): ?>
        <footer class="drawer-foot">
            <div class="drawer-total">
                <span>Subtotal</span>
                <strong><?= rupiah($cs['subtotal']) ?></strong>
            </div>
            <p class="drawer-foot-note">Pajak &amp; ongkir dihitung saat checkout.</p>
            <a href="/geprek-geh/cart" class="btn btn-ghost btn-block" data-close-drawer>Lihat Keranjang</a>
            <?php if (Auth::check()): ?>
                <a href="/geprek-geh/checkout" class="btn btn-primary btn-block">
                    Checkout Sekarang
                    <span class="btn-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </span>
                </a>
            <?php else: ?>
                <a href="/geprek-geh/auth/login" class="btn btn-primary btn-block">Masuk untuk Checkout</a>
            <?php endif; ?>
        </footer>
        <?php endif; ?>
    </aside>
</div>

<script src="/geprek-geh/vendor/lenis/lenis.min.js"></script>
<script src="/geprek-geh/public/js/app.js?v=20260905"></script>
</body>
</html>