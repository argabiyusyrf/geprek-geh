<?php $page_title = 'Daftar'; ?>

<div class="auth-page">
    <div class="auth-shell">
        <div class="auth-brand">
            <div class="auth-brand-inner">
                <a href="/geprek-geh/" class="brand auth-logo">
                    <span class="brand-mark">G</span>
                    <span class="brand-word">Geprek Geh</span>
                </a>
                <p class="auth-brand-tag">Gabung, dan sambal level berapapun siap menantimu.</p>
                <div class="auth-brand-art" aria-hidden="true">
                    <?= product_art('Nasi Geprek Spesial', 'Nasi Geprek', 'auth-art', 300) ?>
                    <span class="auth-brand-chip">New member · promo spesial</span>
                </div>
                <ul class="auth-brand-points">
                    <li><span class="sig"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg></span> Akses lebih cepat ke semua menu</li>
                    <li><span class="sig"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg></span> Riwayat &amp; status pesanan real-time</li>
                </ul>
            </div>
        </div>

        <div class="auth-panel">
            <div class="auth-card">
                <div class="auth-card-core">
                    <span class="eyebrow">Buat akun baru</span>
                    <h1>Daftar <em>sekarang</em></h1>
                    <p class="auth-sub">Buat akun untuk mulai memesan menu favoritmu.</p>

                    <form method="POST" action="/geprek-geh/auth/register">
                        <?= csrf_field() ?>
                        <div class="form-group">
                            <label for="reg-name">Nama Lengkap</label>
                            <input id="reg-name" type="text" name="name" class="input" required placeholder="Nama Anda" autocomplete="name">
                        </div>
                        <div class="form-group">
                            <label for="reg-email">Email</label>
                            <input id="reg-email" type="email" name="email" class="input" required placeholder="you@email.com" autocomplete="email">
                        </div>
                        <div class="form-group">
                            <label for="reg-phone">No. Telepon</label>
                            <input id="reg-phone" type="text" name="phone" class="input" placeholder="08xxx" inputmode="numeric" autocomplete="tel">
                        </div>
                        <div class="form-group">
                            <label for="reg-pass">Password</label>
                            <input id="reg-pass" type="password" name="password" class="input" required minlength="6" placeholder="Minimal 6 karakter" autocomplete="new-password">
                        </div>
                        <button type="submit" class="btn btn-primary btn-block btn-lg">Daftar
                            <span class="btn-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>
                            </span>
                        </button>
                    </form>

                    <p class="auth-link">Sudah punya akun? <a href="/geprek-geh/auth/login">Login</a></p>
                </div>
            </div>
        </div>
    </div>
</div>