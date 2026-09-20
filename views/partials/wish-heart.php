<?php
// Partial: tombol hati wishlist untuk kartu produk.
// Harapkan variabel $p (id, name) dan $wish_ids dari wishlist_ids().
if (empty($wish_ids)) { $wish_ids = wishlist_ids(); }
$is_wished = isset($wish_ids[(int) $p['id']]);
$pid = (int) $p['id'];
?>
<?php if (Auth::check()): ?>
    <form method="POST" action="/wishlist/<?= $pid ?>/toggle" class="wish-form wish-btn-overlay">
        <?= csrf_field() ?>
        <button type="submit" class="product-wish" title="<?= $is_wished ? 'Hapus dari daftar keinginan' : 'Simpan ke daftar keinginan' ?>" aria-label="<?= $is_wished ? 'Hapus dari daftar keinginan' : 'Simpan ke daftar keinginan' ?>">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="<?= $is_wished ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21.2l7.8-7.8 1-1a5.5 5.5 0 0 0 0-7.8z"/></svg>
        </button>
    </form>
<?php else: ?>
    <a href="/auth/login" class="product-wish wish-btn-overlay" title="Masuk untuk menyimpan ke daftar keinginan" aria-label="Masuk untuk menyimpan ke daftar keinginan">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21.2l7.8-7.8 1-1a5.5 5.5 0 0 0 0-7.8z"/></svg>
    </a>
<?php endif; ?>