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

                    <form method="POST" action="/geprek-geh/auth/forgot" id="forgotForm">
                        <?= csrf_field() ?>
                        <div class="form-group reg-field-icon">
                            <label for="fr-email">Email Terdaftar</label>
                            <div class="reg-input-wrap">
                                <svg class="reg-input-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 7l-10 6L2 7"/></svg>
                                <input id="fr-email" type="email" name="email" class="input reg-input" required maxlength="190" placeholder="you@email.com" autocomplete="email" autofocus>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block btn-lg reg-submit" id="forgotSubmit">
                            <span class="reg-submit-text">Kirim Link Reset</span>
                            <span class="btn-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </span>
                            <span class="reg-submit-loading" aria-hidden="true">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                            </span>
                        </button>
                    </form>

                    <p class="auth-link">Ingat passwordnya? <a href="/geprek-geh/auth/login">Login di sini</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    document.getElementById('forgotForm').addEventListener('submit', function(){
        var btn = document.getElementById('forgotSubmit');
        btn.classList.add('is-loading');
        btn.disabled = true;
    });
})();
</script>