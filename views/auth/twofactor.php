<?php $page_title = 'Verifikasi 2FA'; ?>

<div class="auth-page">
    <div class="auth-shell">
        <div class="auth-brand">
            <div class="auth-brand-inner">
                <a href="/geprek-geh/" class="brand auth-logo">
                    <span class="brand-mark">G</span>
                    <span class="brand-word">Geprek Geh</span>
                </a>
                <p class="auth-brand-tag">Lapisan keamanan ekstra untuk akunmu.</p>
                <div class="auth-brand-art" aria-hidden="true">
                    <?= product_art('Pesanan Aman', 'Geprek Original', 'auth-art', 300) ?>
                    <span class="auth-brand-chip">2FA · Authenticator</span>
                </div>
                <ul class="auth-brand-points">
                    <li><span class="sig"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l8 4v6c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6z"/><path d="M9 12l2 2 4-4"/></svg></span> Kode berubah tiap 30 detik</li>
                    <li><span class="sig"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg></span> Tetap aman meski password bocor</li>
                </ul>
            </div>
        </div>

        <div class="auth-panel">
            <a href="/geprek-geh/" class="auth-back" aria-label="Kembali ke beranda">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            </a>
            <div class="auth-card">
                <div class="auth-card-core">
                    <div class="twofa-badge">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <span class="eyebrow">Verifikasi 2 langkah</span>
                    <h1>Masukkan <em>kode</em></h1>
                    <p class="auth-sub">6 digit dari aplikasi authenticator<?= $twofa_name ? ' milik <strong>' . e($twofa_name) . '</strong>' : '' ?>.</p>

                    <form method="POST" action="/geprek-geh/auth/2fa" id="twofa-form">
                        <?= csrf_field() ?>
                        <div class="form-group">
                            <label for="twofa-code">Kode Verifikasi</label>
                            <input id="twofa-code" type="text" name="code" class="input twofa-code-input is-code" inputmode="numeric" pattern="[0-9a-zA-Z]{6,8}" placeholder="••••••" autocomplete="one-time-code" maxlength="8" required autofocus>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block btn-lg reg-submit" id="twofaSubmit">
                            <span class="reg-submit-text">Verifikasi &amp; Masuk</span>
                            <span class="reg-submit-loading" aria-hidden="true">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                            </span>
                        </button>
                    </form>

                    <p class="auth-link">Kode tidak berhasil? Gunakan <a href="/geprek-geh/auth/login">recovery code</a> yang disimpan saat aktivasi.</p>
                    <p class="auth-link"><a href="/geprek-geh/auth/login">Kembali ke halaman login</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    var form = document.getElementById('twofa-form');
    var btn = document.getElementById('twofaSubmit');
    var code = document.getElementById('twofa-code');

    form.addEventListener('submit', function(){
        btn.classList.add('is-loading');
        btn.disabled = true;
    });

    // Hapus karakter selain angka/huruf saat mengetik (recovery code alfanumerik).
    code.addEventListener('input', function(){
        this.value = this.value.toUpperCase().replace(/[^0-9A-Z]/g, '');
    });
})();
</script>