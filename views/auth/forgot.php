<?php $page_title = 'Lupa Password'; $fr_old = $fr_old ?? null;
$fr_method = $fr_old['method'] ?? 'email';
$fr_email  = $fr_old['email'] ?? '';
?>

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
                    <span class="auth-brand-chip">Dua jalur · aman</span>
                </div>
                <ul class="auth-brand-points">
                    <li><span class="sig"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg></span> Link email berlaku 1 jam</li>
                    <li><span class="sig"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg></span> Kata kunci &mdash; tanpa perlu email</li>
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
                    <h1>Pilih cara <em>pulih</em></h1>
                    <p class="auth-sub">Masukkan emailmu, lalu pilih jalur pemulihan favoritmu.</p>

                    <form method="POST" action="/geprek-geh/auth/forgot" id="forgotForm" novalidate>
                        <?= csrf_field() ?>

                        <div class="form-group reg-field-icon">
                            <label for="fr-email">Email Terdaftar</label>
                            <div class="reg-input-wrap">
                                <svg class="reg-input-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 7l-10 6L2 7"/></svg>
                                <input id="fr-email" type="email" name="email" class="input reg-input" required maxlength="190" placeholder="you@email.com" autocomplete="email" autofocus value="<?= e($fr_email) ?>">
                            </div>
                        </div>

                        <fieldset class="form-group" id="methodGroup">
                            <legend class="field-label">Metode Pemulihan</legend>
                            <div class="payment-options">
                                <label class="radio-card" for="m-email">
                                    <input type="radio" id="m-email" name="method" value="email" data-method="email" <?= $fr_method === 'keyword' ? '' : 'checked' ?>>
                                    <span class="radio-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 7l-10 6L2 7"/></svg></span>
                                    <span class="radio-body">
                                        <span class="radio-title">Link via email</span>
                                        <span class="radio-desc">Tautan reset dikirim ke email terdaftar</span>
                                    </span>
                                    <span class="radio-check"></span>
                                </label>
                                <label class="radio-card" for="m-keyword">
                                    <input type="radio" id="m-keyword" name="method" value="keyword" data-method="keyword" <?= $fr_method === 'keyword' ? 'checked' : '' ?>>
                                    <span class="radio-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span>
                                    <span class="radio-body">
                                        <span class="radio-title">Kata kunci akun</span>
                                        <span class="radio-desc">Jawab kata kunci rahasia yang kamu set saat daftar</span>
                                    </span>
                                    <span class="radio-check"></span>
                                </label>
                            </div>
                        </fieldset>

                        <div class="form-group reg-field-icon option-extra<?= $fr_method === 'keyword' ? ' is-visible' : '' ?>" id="keywordGroup" data-method-panel="keyword">
                            <label for="fr-keyword">Kata Kunci Akun</label>
                            <div class="reg-input-wrap reg-input-pass">
                                <svg class="reg-input-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-8 8M18 5l3 3M16 7l1 1M12 6l-4 4a4 4 0 0 0-6 6l14 4 4-4a4 4 0 0 0-4-4l-4-4z" transform="rotate(90 12 12)"/></svg>
                                <input id="fr-keyword" type="password" name="keyword" class="input reg-input" placeholder="Kata kunci rahasiamu" autocomplete="off" maxlength="72">
                            </div>
                            <p class="field-hint">Kata kunci dibuat saat setup akun. Bisa diubah dari Akun → Keamanan.</p>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block btn-lg reg-submit" id="forgotSubmit">
                            <span class="reg-submit-text" id="frSubmitText">Kirim Link Reset</span>
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
    var keywordExtra = document.getElementById('keywordGroup');
    var submitText = document.getElementById('frSubmitText');
    var keywordInput = document.getElementById('fr-keyword');
    var emailInput = document.getElementById('fr-email');

    function sync(){
        var kw = document.querySelector('input[name="method"]:checked').value === 'keyword';
        keywordExtra.classList.toggle('is-visible', kw);
        submitText.textContent = kw ? 'Verifikasi Kata Kunci' : 'Kirim Link Reset';
    }
    document.querySelectorAll('input[name="method"]').forEach(function(r){
        r.addEventListener('change', sync);
    });
    sync();

    document.getElementById('forgotForm').addEventListener('submit', function(){
        var btn = document.getElementById('forgotSubmit');
        btn.classList.add('is-loading');
        btn.disabled = true;
    });
})();
</script>