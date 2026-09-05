<?php
$hero_product = $featured[0] ?? null;
$hero_level = $categories[1] ?? null;
?>

<!-- ── Hero · Editorial Split ───────────────────────── -->
<section class="hero">
    <div class="hero-copy">
        <span class="eyebrow" data-reveal>Menu Geprek Premium</span>
        <h1 class="hero-title" data-reveal>Geprek<br><em>Geh.</em></h1>
        <p class="hero-sub" data-reveal>
            Ayam geprek renyah, dihantam sambal <strong>cabe level sesuai seleramu</strong>.
            Pesan online dalam hitungan detik — kami siapkan panas, antar ke depan pintu.
        </p>
        <div class="hero-cta" data-reveal>
            <a href="/geprek-geh/products" class="btn btn-primary btn-lg magnetic">
                Pesan Sekarang
                <span class="btn-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7M9 7h8v8"/></svg>
                </span>
            </a>
            <a href="#menu" class="btn btn-ghost">Telusuri Menu ↓</a>
        </div>
        <div class="hero-stats" data-reveal>
            <div class="hero-stat"><b>20+</b><span>Menu Signature</span></div>
            <div class="hero-stat"><b>5</b><span>Level Pedas</span></div>
            <div class="hero-stat"><b>30&nbsp;mnt</b><span>Waktu Antar</span></div>
        </div>
    </div>

    <div class="hero-visual" data-reveal data-reveal-delay>
        <div class="hero-plate">
            <div class="hero-plate-core">
                <div class="hero-dish">
                    <span class="hero-dish-label">GG</span>
                    <span class="product-img-placeholder"><svg width="90" height="90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M15.5 11.5a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0z"/><path d="M11 7V3M13.5 4.5L8.5 6.5M15 7.5l-6 1.5"/><path d="M6 1l-1 3M9 2L7.5 4"/></svg></span>
                </div>
                <div class="hero-dish-meta">
                    <div>
                        <h3><?= $hero_product ? e($hero_product['name']) : 'Geprek Original' ?></h3>
                        <span><?= $hero_product ? e($hero_product['category_name']) : 'Sambal khas Geprek Geh' ?></span>
                    </div>
                    <b><?= $hero_product ? rupiah($hero_product['price']) : rupiah(18000) ?></b>
                </div>
            </div>
        </div>
        <div class="hero-card-float">
            <div class="float-core"><svg width="30" height="30" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2c.6 3-1.2 4.4-2.2 6-2.9 4.6-.4 9 2.2 9 4 0 6-2.6 6-5.6 0-2.4-1.3-3.9-2.4-5.4.1 1.9-1.3 2.9-2.3 3.3.6-3-1.3-7.3 1.3-7.3z"/><path d="M9 13.5c.4 2 2 3 3.6 2.6-1.9 3-6.6 1-5.6-4.4-.6 1.8.6 2.6 2 1.8z" opacity="0.6"/></svg></div>
            <div>
                <small><?= $hero_level ? e($hero_level['name']) : 'Level Pedas' ?></small>
                <b>Setan Level 1–5,<br>pilih keberanianmu</b>
            </div>
        </div>
        <div class="hero-chip"><b>✦</b> Level Pedas Customizable</div>
    </div>
</section>

<!-- ── Marquee ─────────────────────────────────────── -->
<div class="marquee" aria-hidden="true">
    <div class="marquee-track">
        <?php $marquee = ['Ayam Geprek', 'Sambal Level', 'Nasi Panas', 'Telur Dadar', 'Es Teh Manis', 'Crispy Renyah', 'Pedas Nikmat'];
        $items = array_merge($marquee, $marquee); ?>
        <?php foreach ($items as $m): ?>
            <span class="marquee-item"><?= e($m) ?> <i>✦</i></span>
        <?php endforeach; ?>
    </div>
</div>

<!-- ── Kategori · Asymmetric Bento ─────────────────── -->
<section class="section" id="menu">
    <div class="section-head">
        <div>
            <span class="eyebrow" data-reveal>Kategori</span>
            <h2 class="section-title" data-reveal>Cari sesuai<br>selera &amp; levelmu</h2>
        </div>
        <a href="/geprek-geh/products" class="section-link" data-reveal>Semua menu
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7M9 7h8v8"/></svg>
        </a>
    </div>
    <div class="bento">
        <?php
        $cat_icons = [
            'Geprek Original' => '<path d="M15.5 11.5a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0z"/><path d="M11 7V3M13.5 4.5L8.5 6.5M15 7.5l-6 1.5"/><path d="M6 1l-1 3M9 2L7.5 4"/>',
            'Geprek Level'    => '<path d="M12 2c.6 3-1.2 4.4-2.2 6-2.9 4.6-.4 9 2.2 9 4 0 6-2.6 6-5.6 0-2.4-1.3-3.9-2.4-5.4.1 1.9-1.3 2.9-2.3 3.3.6-3-1.3-7.3 1.3-7.3z"/>',
            'Geprek Setan'    => '<path d="M12 2c.6 3-1.2 4.4-2.2 6-2.9 4.6-.4 9 2.2 9 4 0 6-2.6 6-5.6 0-2.4-1.3-3.9-2.4-5.4.1 1.9-1.3 2.9-2.3 3.3.6-3-1.3-7.3 1.3-7.3z"/><path d="M9 13.5c.4 2 2 3 3.6 2.6-1.9 3-6.6 1-5.6-4.4-.6 1.8.6 2.6 2 1.8z" opacity="0.6"/>',
            'Nasi Geprek'     => '<path d="M5 3h14M5 3v14a4 4 0 0 0 7 2.5A4 4 0 0 0 19 17V3M5 9h14M9 20.5l-1.5 2M15 20.5L16.5 22.5"/>',
            'Minuman'         => '<path d="M8 3h8M10 3v5l-4 11a2 2 0 0 0 2 2.4h8A2 2 0 0 0 18 19L14 8V3M7.5 14h9"/>',
            'Side Dish'       => '<circle cx="12" cy="12" r="8.5"/><circle cx="8" cy="9" r="1.4"/><circle cx="14" cy="7" r="1.4"/><circle cx="16.5" cy="12" r="1.4"/><circle cx="13" cy="16" r="1.4"/><path d="M6 17l2-2"/>',
        ];
        ?>
        <?php foreach ($categories as $cat): ?>
            <a href="/geprek-geh/products?category=<?= e($cat['slug']) ?>" class="category-card" data-reveal>
                <div class="category-card-core">
                    <span class="category-emoji"><svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><?= $cat_icons[$cat['name']] ?? '<path d="M12 3v10M8 8l4 4 4-4"/>' ?></svg></span>
                    <h3><?= e($cat['name']) ?></h3>
                    <p><?= $cat['product_count'] ?> menu</p>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- ── Favorit · Bento ─────────────────────────────── -->
<?php if (!empty($featured)): ?>
<section class="section">
    <div class="section-head">
        <div>
            <span class="eyebrow" data-reveal>Paling Laris</span>
            <h2 class="section-title" data-reveal>Favorit<br>pelanggan</h2>
        </div>
        <a href="/geprek-geh/products" class="section-link" data-reveal>Lihat semua
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7M9 7h8v8"/></svg>
        </a>
    </div>
    <div class="menu-bento">
        <?php foreach ($featured as $i => $p): ?>
            <article class="product-card <?= $i === 0 ? 'feat-a' : '' ?>" data-reveal>
                <div class="product-card-inner">
                    <a href="/geprek-geh/products/<?= e($p['slug']) ?>" class="product-img">
                        <?php if ($p['image']): ?>
                            <img src="/geprek-geh/assets/uploads/products/<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>">
                        <?php else: ?>
                            <span class="product-img-placeholder"><svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M15.5 11.5a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0z"/><path d="M11 7V3M13.5 4.5L8.5 6.5M15 7.5l-6 1.5"/><path d="M6 1l-1 3M9 2L7.5 4"/></svg></span>
                        <?php endif; ?>
                        <?php if ($p['stock'] <= 0): ?>
                            <span class="stock-badge">Habis</span>
                        <?php endif; ?>
                    </a>
                    <div class="product-info">
                        <span class="product-cat"><?= e($p['category_name']) ?></span>
                        <h3><a href="/geprek-geh/products/<?= e($p['slug']) ?>"><?= e($p['name']) ?></a></h3>
                        <div class="product-price"><?= rupiah($p['price']) ?></div>
                        <?php if ($p['stock'] > 0): ?>
                        <form method="POST" action="/geprek-geh/cart/add">
                            <?= csrf_field() ?>
                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="btn btn-primary">Tambah
                                <span class="btn-icon"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5v14"/></svg></span>
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- ── About strip ─────────────────────────────────── -->
<section class="section">
    <div class="about-strip" data-reveal>
        <div>
            <span class="eyebrow">Kenapa Geprek Geh?</span>
            <h2>Pedas itu <em>seni</em>,<br>dan kami <span class="g">menghantammya</span> setiap hari</h2>
            <p>Dimasak langsung saat pesanan masuk. Jasuke crispy di-smashed dengan sambal bawang segar yang digiling setiap pagi — bukan hari kemarin.</p>
        </div>
        <div class="about-list">
            <div class="about-item" data-reveal>
                <span class="sig"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg></span>
                <div><b>Segar &amp; Saji Cepat</b><span>Dimasak per-pesanan, rata-rata siap dalam 15 menit.</span></div>
            </div>
            <div class="about-item" data-reveal>
                <span class="sig"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h7l-1 8 10-12h-7z"/></svg></span>
                <div><b>Level Terkontrol</b><span>Dari 1 (ringan) sampai Setan — racikan konsisten, setiap porsi.</span></div>
            </div>
            <div class="about-item" data-reveal>
                <span class="sig"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zM23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg></span>
                <div><b>Halal &amp; Bersih</b><span>Bahan premium, dapur higienis, sertifikasi halal.</span></div>
            </div>
        </div>
    </div>
</section>

<!-- ── Terbaru ─────────────────────────────────────── -->
<?php if (!empty($latest)): ?>
<section class="section">
    <div class="section-head">
        <div>
            <span class="eyebrow" data-reveal>Baru Masuk</span>
            <h2 class="section-title" data-reveal>Terbaru<br>dari dapur</h2>
        </div>
    </div>
    <div class="product-grid">
        <?php foreach ($latest as $i => $p): ?>
            <article class="product-card" data-reveal>
                <div class="product-card-inner">
                    <a href="/geprek-geh/products/<?= e($p['slug']) ?>" class="product-img">
                        <?php if ($p['image']): ?>
                            <img src="/geprek-geh/assets/uploads/products/<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>">
                        <?php else: ?>
                            <span class="product-img-placeholder"><svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M15.5 11.5a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0z"/><path d="M11 7V3M13.5 4.5L8.5 6.5M15 7.5l-6 1.5"/><path d="M6 1l-1 3M9 2L7.5 4"/></svg></span>
                        <?php endif; ?>
                        <?php if ($p['stock'] <= 0): ?>
                            <span class="stock-badge">Habis</span>
                        <?php endif; ?>
                    </a>
                    <div class="product-info">
                        <span class="product-cat"><?= e($p['category_name']) ?></span>
                        <h3><a href="/geprek-geh/products/<?= e($p['slug']) ?>"><?= e($p['name']) ?></a></h3>
                        <div class="product-price"><?= rupiah($p['price']) ?></div>
                        <?php if ($p['stock'] > 0): ?>
                        <form method="POST" action="/geprek-geh/cart/add">
                            <?= csrf_field() ?>
                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="btn btn-outline">Tambah
                                <span class="btn-icon"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5v14"/></svg></span>
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>