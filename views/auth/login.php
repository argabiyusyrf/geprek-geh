<?php $page_title = 'Login'; ?>

<div class="auth-page">
    <div class="auth-shell">
        <div class="auth-brand">
            <div class="auth-brand-inner">
                <a href="/geprek-geh/" class="brand auth-logo">
                    <span class="brand-mark">G</span>
                    <span class="brand-word">Geprek Geh</span>
                </a>
                <p class="auth-brand-tag">Pedas itu <em>seni</em>, dan kami menghantamnya setiap hari.</p>
                <div class="auth-brand-art" aria-hidden="true">
                    <?= product_art('Geprek Setan', 'Geprek Level', 'auth-art', 300) ?>
                    <span class="auth-brand-chip">Level 1–5 · Custom</span>
                </div>
                <ul class="auth-brand-points">
                    <li><span class="sig"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg></span> Siap hangat dalam ±15 menit</li>
                    <li><span class="sig"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg></span> Fresh, higienis, halal</li>
                </ul>
            </div>
        </div>

        <div class="auth-panel">
            <div class="auth-card">
                <div class="auth-card-core">
                    <span class="eyebrow">Selamat datang kembali</span>
                    <h1>Masuk ke <em>akunmu</em></h1>
                    <p class="auth-sub">Pesan lebih cepat, lacak pesanan, dan simpan favoritmu.</p>

                    <form method="POST" action="/geprek-geh/auth/login">
                        <?= csrf_field() ?>
                        <div class="form-group">
                            <label for="login-email">Email</label>
                            <input id="login-email" type="email" name="email" class="input" required placeholder="you@email.com" autocomplete="email" autofocus>
                        </div>
                        <div class="form-group">
                            <label for="login-pass">Password</label>
                            <input id="login-pass" type="password" name="password" class="input" required placeholder="••••••••" autocomplete="current-password">
                        </div>
                        <button type="submit" class="btn btn-primary btn-block btn-lg">Login
                            <span class="btn-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg>
                            </span>
                        </button>
                    </form>

                    <p class="auth-link">Belum punya akun? <a href="/geprek-geh/auth/register">Daftar di sini</a></p>
                </div>
            </div>
        </div>
    </div>
</div>