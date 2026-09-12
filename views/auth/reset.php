<?php
$page_title = 'Reset Password';
$selector = $_GET['selector'] ?? '';
$token = $_GET['token'] ?? '';
$valid = (new PasswordResetController())->isValidLink($selector, $token);
?>

<div class="auth-page">
    <div class="auth-shell">
        <div class="auth-brand">
            <div class="auth-brand-inner">
                <a href="/geprek-geh/" class="brand auth-logo">
                    <span class="brand-mark">G</span>
                    <span class="brand-word">Geprek Geh</span>
                </a>
                <p class="auth-brand-tag">Password baru, <em>reset</em> dalam satu klik.</p>
                <div class="auth-brand-art" aria-hidden="true">
                    <?= product_art('Nasi Geprek', 'Nasi Geprek', 'auth-art', 300) ?>
                    <span class="auth-brand-chip">Aman · sekali pakai</span>
                </div>
            </div>
        </div>

        <div class="auth-panel">
            <a href="/geprek-geh/" class="auth-back" aria-label="Kembali ke beranda">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            </a>
            <div class="auth-card">
                <div class="auth-card-core">
                    <span class="eyebrow">Password Baru</span>
                    <h1>Atur <em>ulang</em></h1>

                    <?php if (!$valid): ?>
                        <div class="alert alert-error alert-static">Link reset sudah tidak valid atau kedaluwarsa. Silakan minta link baru.</div>
                        <p class="auth-link"><a href="/geprek-geh/auth/forgot">Minta link baru</a></p>
                    <?php else: ?>
                        <p class="auth-sub">Buat password baru untuk akunmu. Minimal 6 karakter.</p>

                        <form method="POST" action="/geprek-geh/auth/reset" id="resetForm" novalidate>
                            <?= csrf_field() ?>
                            <input type="hidden" name="selector" value="<?= e($selector) ?>">
                            <input type="hidden" name="token" value="<?= e($token) ?>">

                            <div class="form-group reg-field-icon">
                                <label for="rp-pass">Password Baru</label>
                                <div class="reg-input-wrap reg-input-pass">
                                    <svg class="reg-input-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                    <input id="rp-pass" type="password" name="password" class="input reg-input" required minlength="6" maxlength="72" placeholder="Minimal 6 karakter" autocomplete="new-password" autofocus>
                                    <button type="button" class="pass-toggle" data-toggle-pass="rp-pass" aria-label="Tampilkan password">
                                        <svg class="eye-open" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        <svg class="eye-closed" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                    </button>
                                </div>
                                <div class="pass-strength" id="rpStrength">
                                    <div class="pass-strength-bar"><span id="rpStrengthFill"></span></div>
                                    <span class="pass-strength-label" id="rpStrengthLabel"></span>
                                </div>
                            </div>
                            <div class="form-group reg-field-icon">
                                <label for="rp-confirm">Konfirmasi Password</label>
                                <div class="reg-input-wrap reg-input-pass">
                                    <svg class="reg-input-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/><path d="M12 15v3"/></svg>
                                    <input id="rp-confirm" type="password" name="password_confirm" class="input reg-input" required minlength="6" maxlength="72" autocomplete="new-password" placeholder="Ketik ulang">
                                    <button type="button" class="pass-toggle" data-toggle-pass="rp-confirm" aria-label="Tampilkan password">
                                        <svg class="eye-open" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        <svg class="eye-closed" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                    </button>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block btn-lg reg-submit" id="resetSubmit">
                                <span class="reg-submit-text">Simpan Password</span>
                                <span class="btn-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                                </span>
                                <span class="reg-submit-loading" aria-hidden="true">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                                </span>
                            </button>
                        </form>

                        <p class="auth-link"><a href="/geprek-geh/auth/login">Kembali ke login</a></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    function $(sel){ return document.querySelector(sel); }

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

    // ── Password strength meter ──
    var passInput = $('#rp-pass');
    var strengthBar = $('#rpStrengthFill');
    var strengthLabel = $('#rpStrengthLabel');
    var strengthWrap = $('#rpStrength');

    function evaluateStrength(val){
        var score = 0;
        if(val.length >= 6)  score++;
        if(val.length >= 10) score++;
        if(/[A-Z]/.test(val)) score++;
        if(/[0-9]/.test(val)) score++;
        if(/[^A-Za-z0-9]/.test(val)) score++;
        return score;
    }

    passInput.addEventListener('input', function(){
        var val = this.value;
        if(!val){
            strengthWrap.classList.remove('is-active');
            return;
        }
        strengthWrap.classList.add('is-active');
        var score = evaluateStrength(val);
        var labels = ['', 'Lemah', 'Cukup', 'Baik', 'Kuat', 'Sangat kuat'];
        var classes = ['', 'weak', 'fair', 'good', 'strong', 'strong'];
        strengthBar.className = 'pass-strength-fill ' + (classes[score] || '');
        strengthLabel.textContent = labels[score] || '';
        strengthLabel.className = 'pass-strength-label ' + (classes[score] || '');
    });

    // ── Validasi client + submit loading ──
    var form = $('#resetForm');
    var submitBtn = $('#resetSubmit');
    var errEl = document.createElement('div');
    errEl.className = 'field-error';
    errEl.style.display = 'none';
    var confirmWrap = document.getElementById('rp-confirm').closest('.form-group');
    confirmWrap.appendChild(errEl);

    function setInline(msg){
        errEl.style.display = msg ? 'flex' : 'none';
        errEl.textContent = msg || '';
    }

    // ── Live check: konfirmasi password cocok / tidak ──
    var confirmInput = $('#rp-confirm');
    function checkMatch(){
        var cv = confirmInput.value;
        if (!cv){ setInline(''); confirmInput.classList.remove('is-invalid'); return; }
        var ok = cv === passInput.value;
        setInline(ok ? '' : 'Konfirmasi password tidak cocok.');
        confirmInput.classList.toggle('is-invalid', !ok);
    }
    confirmInput.addEventListener('input', checkMatch);
    passInput.addEventListener('input', checkMatch);

    form.addEventListener('submit', function(e){
        var passV = passInput.value;
        var confirmV = $('#rp-confirm').value;
        var msg = null;
        if (passV.length < 6) { msg = 'Password minimal 6 karakter.'; }
        else if (passV.length > 72) { msg = 'Password maksimal 72 karakter.'; }
        else if (confirmV !== passV) { msg = 'Konfirmasi password tidak cocok.'; }

        if (msg){
            e.preventDefault();
            $('#rp-confirm').classList.add('is-invalid');
            setInline(msg);
            return;
        }
        submitBtn.classList.add('is-loading');
        submitBtn.disabled = true;
    });
})();
</script>