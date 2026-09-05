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

<script src="/geprek-geh/vendor/lenis/lenis.min.js"></script>
<script src="/geprek-geh/public/js/app.js?v=20260905"></script>
</body>
</html>