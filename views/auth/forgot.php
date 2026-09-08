<?php $page_title = 'Lupa Password'; ?>

<div class="auth-page">
    <div class="auth-shell">
        <div class="auth-brand">
            <div class="auth-brand-inner">
                <a href="/geprek-geh/" class="brand auth-logo">
                    <span class="brand-mark">G</span>
                    <span class="brand-word">Geprek Geh</span>
                </a>
                <p class="auth-brand-tag">Tenang, kami bantu <em>masuk</em> lagi.</p>
                <div class="auth-brand-art" aria-hidden="true">
                    <?= product_art('Geprek Original', 'Geprek Original', 'auth-art', 300) ?>
                    <span class="auth-brand-chip">Reset · 60 menit</span>
                </div>
                <ul class="auth-brand-points">
                    <li><span class="sig"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg></span> Link reset berlaku 1 jam</li>
                    <li><span class="sig"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg></span> Token sekali pakai</li>
                </ul>
            </div>
        </div>

        <div class="auth-panel">
            <a href="/geprek-geh/" class="auth-back" aria-label="Kembali ke beranda">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            </a>
            <div class="auth-card">
                <div class="auth-card-core">
                    <span class="eyebrow">Lupa Password</span>
                    <h1>Reset <em>password</em></h1>
                    <p class="auth-sub">Masukkan email akunmu. Kami kirim tautan untuk memilih password baru.</p>

                    <form method="POST" action="/geprek-geh/auth/forgot">
                        <?= csrf_field() ?>
                        <div class="form-group">
                            <label for="fr-email">Email Terdaftar</label>
                            <input id="fr-email" type="email" name="email" class="input" required placeholder="you@email.com" autocomplete="email" autofocus>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block btn-lg">Kirim Link Reset
                            <span class="btn-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </span>
                        </button>
                    </form>

                    <p class="auth-link">Ingat passwordnya? <a href="/geprek-geh/auth/login">Login di sini</a></p>
                </div>
            </div>
        </div>
    </div>
</div>