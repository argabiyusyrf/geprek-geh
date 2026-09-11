<?php
$page_title = $product['name'];
$page_description = mb_substr(strip_tags($product['description'] ?? ''), 0, 160);
if (!empty($product['image'])) {
    $og_image = 'http' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 's' : '') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/geprek-geh/assets/uploads/products/' . $product['image'];
}
$og_type = 'product';
$page_jsonld = SeoController::productJsonLd($product);
$out_stock = $product['stock'] <= 0;
?>

<div class="pd-wrap">
    <nav class="pd-crumb" aria-label="Breadcrumb">
        <a href="/geprek-geh/">Beranda</a><i>/</i>
        <a href="/geprek-geh/products">Menu</a><i>/</i>
        <a href="/geprek-geh/products?category=<?= e($product['category_slug']) ?>"><?= e($product['category_name']) ?></a><i>/</i>
        <span><?= e($product['name']) ?></span>
    </nav>

    <div class="pd">
        <!-- Visual -->
        <div class="pd-visual" data-pd-visual>
            <?php if ($product['image']): ?>
                <img class="pd-photo" src="/geprek-geh/assets/uploads/products/<?= e($product['image']) ?>" alt="<?= e($product['name']) ?>">
            <?php else: ?>
                <?= product_art($product['name'], $product['category_name'], 'pd-art', 900) ?>
            <?php endif; ?>

            <?php if ($product['is_featured']): ?>
                <span class="pd-chip pd-chip--feat">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3 6 6 .5-4.5 4 1.3 6L12 16.7 6.2 19.5l1.3-6L3 9.5 9 9z"/></svg>
                    Menu Populer
                </span>
            <?php endif; ?>

            <div class="pd-float pd-float--stock <?= $out_stock ? 'is-out' : '' ?>">
                <span class="pd-dot"></span>
                <?= $out_stock ? 'Habis hari ini' : ($product['stock'] <= 5 ? "Sisa {$product['stock']} porsi" : 'Siap diantar') ?>
            </div>

            <div class="pd-caption">
                <span><?= e($product['category_name']) ?></span>
                <span><?= $product['is_featured'] ? 'Signature Menu' : 'Menu Geprek Geh' ?></span>
            </div>
        </div>

        <!-- Info -->
        <div class="pd-info">
            <span class="eyebrow"><?= e($product['category_name']) ?></span>
            <h1 class="pd-title"><?= e($product['name']) ?></h1>

            <?php if ($product['description']): ?>
                <p class="pd-desc"><?= nl2br(e($product['description'])) ?></p>
            <?php endif; ?>

            <div class="pd-legenda">
                <div class="pd-legenda-item">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                    <span><b><?= $product['stock'] ?></b> porsi tersedia di stok hari ini</span>
                </div>
                <div class="pd-legenda-item">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12h20"/><path d="M20 12v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-8"/><path d="M4 6v2"/><path d="M20 6v2"/><path d="M8 3v2"/><path d="M12 3v2"/><path d="M16 3v2"/></svg>
                    <span>Dimasak <b>setelah pesanan masuk</b> &mdash; siap &plusmn;15 menit</span>
                </div>
                <div class="pd-legenda-item">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.07-2.14-.22-4.05 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.15.43-2.29 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>
                    <span>Level sambal <b>bisa dikustom</b> 1&ndash;5, lihat skala di bawah</span>
                </div>
                <div class="pd-legenda-item">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2v6a6 6 0 0 0 12 0V2M6 2h4M18 2h-4"/><path d="M6 2v6L4 6M18 2v6l2-2"/></svg>
                    <span>Antar hangat <b>&plusmn;30 menit (estimasi area lokal)</b></span>
                </div>
                <div class="pd-legenda-item">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M19 7V4a1 1 0 0 0-1-1H5a2 2 0 0 0 0 4h15a1 1 0 0 1 1 1v4h-3a2 2 0 0 0 0 4h3a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1"/><path d="M3 5v14a2 2 0 0 0 2 2h15a1 1 0 0 0 1-1v-4"/></svg>
                    <span>Bayar <b>BNI, ShopeePay, atau COD</b> &mdash; tanpa biaya tersembunyi</span>
                </div>
            </div>

            <div class="pd-levels">
                <div class="pd-levels-head">
                    <span class="pd-price-label">Takaran pedas</span>
                    <span class="pd-levels-flag">Bisa custom</span>
                </div>
                <div class="pd-levels-track" role="img" aria-label="Skala level pedas 1 hingga 5">
                    <span class="pd-level"><b>1</b><i>Ringan</i></span>
                    <span class="pd-level"><b>2</b><i>Hangat</i></span>
                    <span class="pd-level is-active"><b>3</b><i>Standar</i></span>
                    <span class="pd-level"><b>4</b><i>Pedas</i></span>
                    <span class="pd-level"><b>5</b><i>Setan</i></span>
                </div>
                <p class="pd-tiny">Standar masakan kami level 3. Tulis tingkat level kesukaanmu di <b>catatan pesanan</b> saat checkout.</p>
            </div>

            <?php
                $tax_amount = (int) round($product['price'] * ($app['tax_rate'] ?? 0.11));
                $ship       = (int) ($app['shipping'] ?? 0);
            ?>
            <div class="pd-buy" data-price="<?= (int)$product['price'] ?>" data-tax="<?= $tax_amount ?>" data-shipping="<?= $ship ?>">
                <div class="pd-price">
                    <span class="pd-price-label">Harga per porsi</span>
                    <strong><?= rupiah($product['price']) ?></strong>
                </div>

                <div class="pd-breakdown">
                    <div class="pd-line">
                        <span>Subtotal &middot; <em id="pdQtyLabel">1&times; porsi</em></span>
                        <b id="pdSubtotal"><?= rupiah($product['price']) ?></b>
                    </div>
                    <div class="pd-line pd-line--sub">
                        <span>Pajak 11% (&plusmn;<?= rupiah($tax_amount) ?>/porsi)</span>
                        <b>+ <?= rupiah($ship) ?> ongkir flat</b>
                    </div>
                </div>
                <p class="pd-tiny">Total akhir pajak + ongkir dihitung dan tampil jelas di halaman checkout &mdash; tanpa biaya tersembunyi.</p>

                <?php if ($product['stock'] > 0): ?>
                <form method="POST" action="/geprek-geh/cart/add" class="pd-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <div class="pd-qty">
                        <button type="button" class="pd-qty-btn" aria-label="Kurangi" onclick="changeQty(-1)">&minus;</button>
                        <input type="number" name="quantity" id="qty" value="1" min="1" max="<?= $product['stock'] ?>" class="pd-qty-input">
                        <button type="button" class="pd-qty-btn" aria-label="Tambah" onclick="changeQty(1)">+</button>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg pd-add">
                        Tambah ke Keranjang
                        <span class="btn-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1.5"/><circle cx="19" cy="21" r="1.5"/><path d="M2.5 3h2l2.6 12.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L22.5 7H6"/></svg>
                        </span>
                    </button>
                </form>
                <?php else: ?>
                    <button type="button" class="btn btn-lg pd-add is-soldout" disabled>Stok Habis</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ── Reviews ──────────────────────────────────────── -->
<section class="section">
    <div class="section-head">
        <div>
            <span class="eyebrow" data-reveal>Ulasan</span>
            <h2 class="section-title" data-reveal>Yang bilang<br>apa</h2>
        </div>
        <?php if ($review_stats['review_count'] > 0): ?>
        <div class="review-summary" data-reveal>
            <div class="review-stars">
                <?php for ($s = 1; $s <= 5; $s++): ?>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="<?= $s <= round($review_stats['avg_rating']) ? '#D43E1B' : 'none' ?>" stroke="#D43E1B" stroke-width="1.8"><path d="M12 2l3 6 6 .5-4.5 4 1.3 6L12 16.7 6.2 19.5l1.3-6L3 9.5 9 9z"/></svg>
                <?php endfor; ?>
            </div>
            <span><?= number_format($review_stats['avg_rating'], 1) ?> / 5 &middot; <?= $review_stats['review_count'] ?> ulasan</span>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($review_stats['review_count'] > 0): ?>
    <div class="review-dist" data-reveal>
        <div class="review-dist-head">
            <h3 class="review-dist-title">Rincian penilaian</h3>
            <span class="review-dist-verified">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3 6 6 .5-4.5 4 1.3 6L12 16.7 6.2 19.5l1.3-6L3 9.5 9 9z"/></svg>
                Semua ulasan asli, tampil apa adanya
            </span>
        </div>
        <div class="review-dist-bars">
            <?php for ($r = 5; $r >= 1; $r--): ?>
            <?php $pct = $review_stats['review_count'] > 0 ? round($rating_dist[$r] / $review_stats['review_count'] * 100) : 0; ?>
            <div class="review-dist-row">
                <span class="review-dist-label"><?= $r ?><span class="review-dist-star" aria-hidden="true">&#9733;</span></span>
                <div class="review-dist-track"><i style="width:<?= $pct ?>%"></i></div>
                <span class="review-dist-count"><?= $rating_dist[$r] ?></span>
            </div>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (Auth::check()): ?>
        <?php if ($has_delivered || $my_review): ?>
    <div class="review-form-wrap" data-reveal>
        <div class="review-form-head">
            <span class="review-form-avatar"><?= e(mb_strtoupper(mb_substr($_SESSION['user_name'], 0, 1))) ?></span>
            <div>
                <strong>Tulis Ulasan</strong>
                <?php if ($my_review): ?>
                    <span class="review-form-note">Kamu sudah review — update di bawah.</span>
                <?php else: ?>
                    <span class="review-form-note">Tulis pengalamanmu setelah pesanan diterima.</span>
                <?php endif; ?>
            </div>
        </div>
        <form method="POST" action="/geprek-geh/reviews" class="review-form">
            <?= csrf_field() ?>
            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
            <div class="review-rating-input" data-rating-input>
                <?php for ($r = 1; $r <= 5; $r++): ?>
                    <button type="button" class="review-star-btn <?= ($my_review && $r <= $my_review['rating']) || (!$my_review && $r <= 4) ? 'is-active' : '' ?>"
                            data-star="<?= $r ?>" aria-label="<?= $r ?> bintang">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2l3 6 6 .5-4.5 4 1.3 6L12 16.7 6.2 19.5l1.3-6L3 9.5 9 9z"/></svg>
                    </button>
                <?php endfor; ?>
                <input type="hidden" name="rating" value="<?= $my_review ? $my_review['rating'] : 4 ?>">
            </div>
            <textarea name="comment" class="input review-textarea" rows="3" placeholder="Ceritakan pengalamanmu... (opsional)"><?= e($my_review['comment'] ?? '') ?></textarea>
            <button type="submit" class="btn btn-primary">
                <?= $my_review ? 'Update Review' : 'Kirim Review' ?>
                <span class="btn-icon"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></span>
            </button>
        </form>
    </div>
        <?php else: ?>
    <div class="review-purchase-hint" data-reveal>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4zM3 6h18M16 10a4 4 0 0 1-8 0"/></svg>
        <span>Beli produk ini, lalu tulis ulasanmu setelah pesanan diterima.</span>
    </div>
        <?php endif; ?>
    <?php else: ?>
    <div class="review-login-hint" data-reveal>
        <a href="/geprek-geh/auth/login">Masuk</a> untuk menulis ulasan.
    </div>
    <?php endif; ?>

    <?php if (empty($reviews)): ?>
        <div class="empty-state empty-state--compact">
            <span class="ghost">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="0.9" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            </span>
            <h3>Belum ada ulasan</h3>
            <p>Jadikan yang pertama berbagi pengalaman!</p>
        </div>
    <?php else: ?>
        <div class="review-list" data-reveal-stagger>
            <?php foreach ($reviews as $rv): ?>
                <div class="review-card" data-reveal>
                    <div class="review-card-head">
                        <span class="review-avatar"><?= e(mb_strtoupper(mb_substr($rv['user_name'], 0, 1))) ?></span>
                        <div>
                            <strong><?= e($rv['user_name']) ?></strong>
                            <?php if (in_array($rv['user_id'], $review_buyer_ids)): ?>
                                <span class="review-verified-badge">Terverifikasi Pembeli</span>
                            <?php endif; ?>
                            <span class="review-date"><?= time_ago($rv['created_at']) ?></span>
                        </div>
                        <div class="review-stars">
                            <?php for ($s = 1; $s <= 5; $s++): ?>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="<?= $s <= $rv['rating'] ? '#D43E1B' : 'none' ?>" stroke="#D43E1B" stroke-width="1.8"><path d="M12 2l3 6 6 .5-4.5 4 1.3 6L12 16.7 6.2 19.5l1.3-6L3 9.5 9 9z"/></svg>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <?php if ($rv['comment']): ?>
                        <p class="review-body"><?= e($rv['comment']) ?></p>
                    <?php endif; ?>
                    <?php if (Auth::id() === $rv['user_id']): ?>
                        <form method="POST" action="/geprek-geh/reviews/<?= $rv['id'] ?>/delete" class="review-delete" data-confirm="Hapus ulasan ini?">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-ghost btn-dangerghost">Hapus</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php if (!empty($related)): ?>
<section class="section">
    <div class="section-head">
        <div>
            <span class="eyebrow" data-reveal>Lengkapi</span>
            <h2 class="section-title" data-reveal>Menu serupa<br>&amp; favorit</h2>
        </div>
        <a href="/geprek-geh/products" class="section-link" data-reveal>Semua menu
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7M9 7h8v8"/></svg>
        </a>
    </div>
    <div class="product-grid">
        <?php foreach ($related as $p):
            $out_stock = $p['stock'] <= 0;
        ?>
            <article class="product-card" data-reveal>
                <div class="product-card-core">
                    <a class="product-img" href="/geprek-geh/products/<?= e($p['slug']) ?>">
                        <?php if ($p['image']): ?>
                            <img src="/geprek-geh/assets/uploads/products/<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
                        <?php else: ?>
                            <?= product_art($p['name'], $p['category_name'], '', 240) ?>
                        <?php endif; ?>
                        <?php if ($out_stock): ?>
                            <span class="product-badges"><span class="badge badge--red">Habis</span></span>
                        <?php endif; ?>
                    </a>
                    <div class="product-body">
                        <span class="product-cat"><?= e($p['category_name']) ?></span>
                        <h3 class="product-title"><a href="/geprek-geh/products/<?= e($p['slug']) ?>"><?= e($p['name']) ?></a></h3>
                        <?php if ($p['review_count'] > 0): ?>
                        <div class="product-rating">
                            <?php for ($s = 1; $s <= 5; $s++): ?>
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="<?= $s <= round($p['avg_rating']) ? '#D43E1B' : 'none' ?>" stroke="#D43E1B" stroke-width="1.8"><path d="M12 2l3 6 6 .5-4.5 4 1.3 6L12 16.7 6.2 19.5l1.3-6L3 9.5 9 9z"/></svg>
                            <?php endfor; ?>
                            <span class="product-rating-count">(<?= $p['review_count'] ?>)</span>
                        </div>
                        <?php endif; ?>
                        <?php if ($p['description']): ?>
                            <p class="product-desc"><?= e(mb_strimwidth($p['description'], 0, 84, '…')) ?></p>
                        <?php endif; ?>
                        <div class="product-foot">
                            <b class="product-price"><?= rupiah($p['price']) ?></b>
                            <?php if (!$out_stock): ?>
                                <form method="POST" action="/geprek-geh/cart/add" class="product-add-form">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="product-add" title="Tambah ke keranjang">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>