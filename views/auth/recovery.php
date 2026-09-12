<?php $page_title = 'Pemulihan Akun'; ?>

<div class="auth-page">
    <div class="auth-shell">
        <div class="auth-brand">
            <div class="auth-brand-inner">
                <a href="/geprek-geh/" class="brand auth-logo">
                    <span class="brand-mark">G</span>
                    <span class="brand-word">Geprek Geh</span>
                </a>
                <p class="auth-brand-tag">Kata kunci benar — <em>selamat</em> kembali.</p>
                <div class="auth-brand-art" aria-hidden="true">
                    <?= product_art('Es Teh Manis', 'Es Teh Manis', 'auth-art', 300) ?>
                    <span class="auth-brand-chip">Verifikasi · <?= (int) ($rc_exp ?? 0) ?> menit</span>
                </div>
                <ul class="auth-brand-points">
                    <li><span class="sig"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg></span> Sesi pemulihan berakhir otomatis</li>
                    <li><span class="sig"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg></span> 2FA tetap dijalankan bila aktif</li>
                </ul>
            </div>
        </div>

        <div class="auth-panel">
            <a href="/geprek-geh/auth/forgot" class="auth-back" aria-label="Lupa password">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            </a>
            <div class="auth-card">
                <div class="auth-card-core">
                    <span class="eyebrow">Pemulihan Akun</span>
                    <h1>Kamu berhasil <em>dikenali</em></h1>
                    <p class="auth-sub">Kata kunci cocok untuk <strong><?= e($rc_name ?? 'akunmu') ?></strong>
                        <span class="recovery-email"><?= e($rc_email ?? '') ?></span>.
                        Pilih apa yang ingin kamu lakukan:</p>

                    <div class="payment-options">
                        <form method="POST" action="/geprek-geh/auth/recovery/login" id="rcLoginForm">
                            <?= csrf_field() ?>
                            <label class="radio-card" for="rc-remember">
                                <input type="checkbox" id="rc-remember" name="remember" value="1">
                                <span class="radio-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M15 7l6 6-6 6M3 5v5a4 4 0 0 0 4 4h14"/><path d="M9 19l-6-6"/></svg></span>
                                <span class="radio-body">
                                    <span class="radio-title">Login langsung</span>
                                    <span class="radio-desc">Langsung masuk ke akun seperti biasa</span>
                                </span>
                                <span class="radio-check"></span>
                            </label>
                            <button type="submit" class="btn btn-primary btn-block btn-lg reg-submit" id="rcLoginSubmit">
                                <span class="reg-submit-text">Masuk ke Akun Saya</span>
                                <span class="btn-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                </span>
                                <span class="reg-submit-loading" aria-hidden="true">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                                </span>
                            </button>
                        </form>

                        <form method="POST" action="/geprek-geh/auth/recovery/ubah" id="rcChangeForm">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-outline btn-block btn-lg">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 4v6h6M23 20v-6h-6"/><path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15"/></svg>
                                &nbsp;Ubah Password
                            </button>
                        </form>
                    </div>

                    <p class="auth-link"><a href="/geprek-geh/auth/forgot" id="rcAbandon">Bukan ini — mulai lagi dari awal</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    document.getElementById('rcLoginForm').addEventListener('submit', function(){
        var btn = document.getElementById('rcLoginSubmit');
        btn.classList.add('is-loading');
        btn.disabled = true;
    });
})();
</script>