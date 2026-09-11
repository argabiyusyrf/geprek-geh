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
        <div class="footer-col">
            <h4>Legal</h4>
            <a href="/geprek-geh/pages/terms">Syarat &amp; Ketentuan</a>
            <a href="/geprek-geh/pages/privacy">Kebijakan Privasi</a>
            <a href="#" data-cookie-open>Pengaturan Cookie</a>
        </div>
    </div>
    <div class="footer-base">
        <span>© <?= date('Y') ?> Geprek Geh. Semua hak dilindungi.</span>
        <span>Dibuat dengan pedas di Indonesia</span>
    </div>
</footer>

<!-- ── Cookie consent banner ─────────────────────────── -->
<div class="cookie-bar" id="cookie-bar" role="region" aria-label="Cookie" aria-hidden="true">
    <div class="cookie-bar-inner">
        <div class="cookie-bar-body">
            <strong>Kami pakai cookie 🍪</strong>
            <p>Cookie membantu kami mengingat keranjang dan sesi loginmu, serta meningkatkan pengalaman belanja. Pelajari lebih lanjut di <a href="/geprek-geh/pages/privacy" class="cookie-link">Kebijakan Privasi</a>.</p>
        </div>
        <div class="cookie-bar-actions">
            <button type="button" class="btn btn-sm btn-ghost" data-cookie-decline aria-label="Tolak cookie non-esensial">Tolak</button>
            <button type="button" class="btn btn-sm btn-primary" data-cookie-accept aria-label="Setuju dan lanjut">Setuju</button>
        </div>
    </div>
</div>

<!-- ── Cart drawer global ───────────────────────────── -->
<div class="drawer" id="cart-drawer" aria-hidden="true">
    <div class="drawer-scrim" data-close-drawer></div>
    <aside class="drawer-panel" role="dialog" aria-modal="true" aria-label="Keranjang belanja" id="cart-drawer-panel">
        <?php require __DIR__ . '/../cart/drawer.php'; ?>
    </aside>
</div>

<script src="/geprek-geh/vendor/lenis/lenis.min.js"></script>
<script src="/geprek-geh/vendor/qrcode/qrcode.js"></script>
<script src="/geprek-geh/public/js/app.js?v=20260911j"></script>
</body>
</html>