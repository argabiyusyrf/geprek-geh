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
                    <li><span class="sig"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg></span> Promo eksklusif member baru</li>
                </ul>
            </div>
        </div>

        <div class="auth-panel">
            <a href="/geprek-geh/" class="auth-back" aria-label="Kembali ke beranda">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            </a>
            <div class="auth-card">
                <div class="auth-card-core">
                    <span class="eyebrow">Buat akun baru</span>
                    <h1>Daftar <em>sekarang</em></h1>
                    <p class="auth-sub">Mulai pesan favoritmu dalam hitungan detik.</p>

                    <?php if (!empty($reg_errors['_global'])): ?>
                        <div class="alert alert-error alert-static"><?= e($reg_errors['_global']) ?></div>
                    <?php endif; ?>

                    <form method="POST" action="/geprek-geh/auth/register" id="registerForm" novalidate>
                        <?= csrf_field() ?>

                        <div class="reg-fields">
                            <div class="form-group reg-field-icon">
                                <label for="reg-name">Nama Lengkap</label>
                                <div class="reg-input-wrap">
                                    <svg class="reg-input-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                    <input id="reg-name" type="text" name="name" class="input reg-input<?= !empty($reg_errors['name']) ? ' is-invalid' : '' ?>" required maxlength="100" placeholder="Nama Anda" autocomplete="name" autofocus value="<?= e($reg_old['name'] ?? '') ?>">
                                </div>
                                <div class="field-error<?= !empty($reg_errors['name']) ? '' : '" hidden' ?>" data-for="reg-name"><?= e($reg_errors['name'] ?? '') ?></div>
                            </div>

                            <div class="form-group reg-field-icon">
                                <label for="reg-email">Email</label>
                                <div class="reg-input-wrap">
                                    <svg class="reg-input-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 7l-10 6L2 7"/></svg>
                                    <input id="reg-email" type="email" name="email" class="input reg-input<?= !empty($reg_errors['email']) ? ' is-invalid' : '' ?>" required maxlength="190" placeholder="you@email.com" autocomplete="email" value="<?= e($reg_old['email'] ?? '') ?>">
                                </div>
                                <div class="field-error<?= !empty($reg_errors['email']) ? '' : '" hidden' ?>" data-for="reg-email"><?= e($reg_errors['email'] ?? '') ?></div>
                            </div>

                            <div class="form-group reg-field-icon">
                                <label for="reg-phone">No. Telepon <span class="label-optional">(opsional)</span></label>
                                <div class="reg-input-wrap">
                                    <svg class="reg-input-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2"/><path d="M12 18h.01"/></svg>
                                    <input id="reg-phone" type="tel" name="phone" class="input reg-input<?= !empty($reg_errors['phone']) ? ' is-invalid' : '' ?>" maxlength="15" placeholder="08xxx" inputmode="numeric" autocomplete="tel" value="<?= e($reg_old['phone'] ?? '') ?>">
                                </div>
                                <div class="field-error<?= !empty($reg_errors['phone']) ? '' : '" hidden' ?>" data-for="reg-phone"><?= e($reg_errors['phone'] ?? '') ?></div>
                            </div>

                            <div class="form-group reg-field-icon">
                                <label for="reg-pass">Password</label>
                                <div class="reg-input-wrap reg-input-pass">
                                    <svg class="reg-input-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                    <input id="reg-pass" type="password" name="password" class="input reg-input<?= !empty($reg_errors['password']) ? ' is-invalid' : '' ?>" required minlength="6" maxlength="72" placeholder="Minimal 6 karakter" autocomplete="new-password">
                                    <button type="button" class="pass-toggle" data-toggle-pass="reg-pass" aria-label="Tampilkan password">
                                        <svg class="eye-open" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        <svg class="eye-closed" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                    </button>
                                </div>
                                <div class="pass-strength" id="passStrength">
                                    <div class="pass-strength-bar"><span id="passStrengthFill"></span></div>
                                    <span class="pass-strength-label" id="passStrengthLabel"></span>
                                </div>
                                <div class="field-error<?= !empty($reg_errors['password']) ? '' : '" hidden' ?>" data-for="reg-pass"><?= e($reg_errors['password'] ?? '') ?></div>
                            </div>

                            <div class="form-group reg-field-icon">
                                <label for="reg-confirm">Konfirmasi Password</label>
                                <div class="reg-input-wrap reg-input-pass">
                                    <svg class="reg-input-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/><path d="M12 15v3"/></svg>
                                    <input id="reg-confirm" type="password" name="password_confirm" class="input reg-input<?= !empty($reg_errors['password_confirm']) ? ' is-invalid' : '' ?>" required minlength="6" maxlength="72" placeholder="Ulangi password" autocomplete="new-password">
                                    <button type="button" class="pass-toggle" data-toggle-pass="reg-confirm" aria-label="Tampilkan password">
                                        <svg class="eye-open" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        <svg class="eye-closed" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                    </button>
                                </div>
                                <div class="field-error<?= !empty($reg_errors['password_confirm']) ? '' : '" hidden' ?>" data-for="reg-confirm"><?= e($reg_errors['password_confirm'] ?? '') ?></div>
                            </div>
                        </div>

                        <label class="reg-terms">
                            <input type="checkbox" name="terms" id="reg-terms" value="1"<?= !empty($reg_old['terms']) ? ' checked' : '' ?>>
                            <span class="reg-terms-box">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                            </span>
                            <span class="reg-terms-text">Saya setuju dengan <a href="/geprek-geh/pages/terms" target="_blank">Syarat &amp; Ketentuan</a> dan <a href="/geprek-geh/pages/privacy" target="_blank">Kebijakan Privasi</a>.</span>
                        </label>
                        <div class="field-error<?= !empty($reg_errors['terms']) ? '' : '" hidden' ?>" data-for="reg-terms"><?= e($reg_errors['terms'] ?? '') ?></div>

                        <button type="submit" class="btn btn-primary btn-block btn-lg reg-submit" id="regSubmit">
                            <span class="reg-submit-text">Buat Akun</span>
                            <span class="btn-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>
                            </span>
                            <span class="reg-submit-loading" aria-hidden="true">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                            </span>
                        </button>
                    </form>

                    <div class="reg-divider">
                        <span>atau</span>
                    </div>

                    <a href="/geprek-geh/auth/login" class="btn btn-outline btn-block btn-lg reg-login-alt">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg>
                        Sudah punya akun? Login
                    </a>
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

    // ── Inline error clearing (termasuk error dari server) ──
    document.querySelectorAll('.field-error[data-for]').forEach(function(el){
        var input = el.dataset.for === 'reg-terms'
            ? $('#reg-terms')
            : document.getElementById(el.dataset.for);
        if (input) input.addEventListener('input', function(){ setErr(input, null); });
    });

    function setErr(input, msg){
        var el = document.querySelector('.field-error[data-for="' + input.id + '"]');
        if (el){ el.textContent = msg || ''; el.hidden = !msg; }
        input.classList.toggle('is-invalid', !!msg);
    }

    // ── Password strength meter ──
    var passInput = $('#reg-pass');
    var strengthBar = $('#passStrengthFill');
    var strengthLabel = $('#passStrengthLabel');
    var strengthWrap = $('#passStrength');

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

    // ── Client-side validation & submit loading ──
    var form = $('#registerForm');
    var submitBtn = $('#regSubmit');

    form.addEventListener('submit', function(e){
        var nameV = $('#reg-name').value.trim();
        var emailV = $('#reg-email').value.trim();
        var phoneRaw = $('#reg-phone').value.trim();
        var passV = passInput.value;
        var confirmV = $('#reg-confirm').value;
        var termsOk = $('#reg-terms').checked;

        var errors = [];
        if (nameV.length < 2) { errors.push(['reg-name', 'Nama lengkap minimal 2 karakter.']); }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(emailV)) { errors.push(['reg-email', 'Format email tidak valid.']); }
        if (phoneRaw && !/^(08|628)\d{8,11}$/.test(phoneRaw.replace(/\D/g, ''))) { errors.push(['reg-phone', 'Nomor telepon tidak valid. Contoh: 081234567890.']); }
        if (passV.length < 6) { errors.push(['reg-pass', 'Password minimal 6 karakter.']); }
        else if (passV.length > 72) { errors.push(['reg-pass', 'Password maksimal 72 karakter.']); }
        if (confirmV !== passV) { errors.push(['reg-confirm', 'Konfirmasi password tidak cocok.']); }
        if (!termsOk) { errors.push(['reg-terms', 'Harap setujui Syarat & Ketentuan dan Kebijakan Privasi.']); }

        errors.forEach(function(x){ setErr(document.getElementById(x[0]), x[1]); });
        if (errors.length){
            e.preventDefault();
            var first = document.getElementById(errors[0][0]);
            if (first) first.focus();
            return;
        }
        submitBtn.classList.add('is-loading');
        submitBtn.disabled = true;
    });
})();
</script>