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
            <a href="/geprek-geh/" class="auth-back" aria-label="Kembali ke beranda">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            </a>
            <div class="auth-card">
                <div class="auth-card-core">
                    <span class="eyebrow">Selamat datang kembali</span>
                    <h1>Masuk ke <em>akunmu</em></h1>
                    <p class="auth-sub">Pesan lebih cepat, lacak pesanan, dan simpan favoritmu.</p>

                    <form method="POST" action="/geprek-geh/auth/login" id="loginForm">
                        <?= csrf_field() ?>

                        <div class="reg-fields">
                            <div class="form-group reg-field-icon">
                                <label for="login-email">Email</label>
                                <div class="reg-input-wrap">
                                    <svg class="reg-input-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 7l-10 6L2 7"/></svg>
                                    <input id="login-email" type="email" name="email" class="input reg-input" required placeholder="you@email.com" autocomplete="email" autofocus value="<?= e($login_old['email'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="form-group reg-field-icon">
                                <label for="login-pass">Password</label>
                                <div class="reg-input-wrap reg-input-pass">
                                    <svg class="reg-input-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                    <input id="login-pass" type="password" name="password" class="input reg-input" required placeholder="Masukkan password" autocomplete="current-password">
                                    <button type="button" class="pass-toggle" data-toggle-pass="login-pass" aria-label="Tampilkan password">
                                        <svg class="eye-open" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        <svg class="eye-closed" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="login-extras">
                            <label class="reg-terms login-remember">
                                <input type="checkbox" name="remember" value="1">
                                <span class="reg-terms-box">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                                </span>
                                <span class="reg-terms-text">Ingat saya</span>
                            </label>
                            <a href="/geprek-geh/auth/forgot" class="login-forgot">Lupa password?</a>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block btn-lg reg-submit" id="loginSubmit">
                            <span class="reg-submit-text">Login</span>
                            <span class="btn-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg>
                            </span>
                            <span class="reg-submit-loading" aria-hidden="true">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                            </span>
                        </button>
                    </form>

                    <div class="reg-divider">
                        <span>atau</span>
                    </div>

                    <a href="/geprek-geh/auth/register" class="btn btn-outline btn-block btn-lg reg-login-alt">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>
                        Belum punya akun? Daftar
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    // ── Password visibility toggle ──
    document.querySelectorAll('[data-toggle-pass]').forEach(function(btn){
        btn.addEventListener('click', function(){
            var input = document.getElementById(this.dataset.togglePass);
            var isPass = input.type === 'password';
            input.type = isPass ? 'text' : 'password';
            this.classList.toggle('is-visible', isPass);
            input.focus();
        });
    });

    // ── Submit loading state ──
    var form = document.getElementById('loginForm');
    var submitBtn = document.getElementById('loginSubmit');
    form.addEventListener('submit', function(){
        submitBtn.classList.add('is-loading');
        submitBtn.disabled = true;
    });
})();
</script>
