<?php $page_title = 'Lengkapi Akun'; $setup_errors = $setup_errors ?? []; $setup_old = $setup_old ?? []; ?>

<div class="auth-page">
    <div class="auth-shell">
        <div class="auth-brand">
            <div class="auth-brand-inner">
                <a href="/geprek-geh/" class="brand auth-logo">
                    <span class="brand-mark">G</span>
                    <span class="brand-word">Geprek Geh</span>
                </a>
                <p class="auth-brand-tag">Langkah terakhir — <em>2 menit</em>.</p>
                <div class="auth-brand-art" aria-hidden="true">
                    <?= product_art('Paket Hemat', 'Paket Hemat', 'auth-art', 300) ?>
                    <span class="auth-brand-chip">Setup · sekali</span>
                </div>
                <ul class="auth-brand-points">
                    <li><span class="sig"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg></span> Kata kunci melindungi akunmu</li>
                    <li><span class="sig"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg></span> Alamat opsional untuk checkout lebih cepat</li>
                </ul>
            </div>
        </div>

        <div class="auth-panel">
            <a href="/geprek-geh/" class="auth-back" aria-label="Lewati setup">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            </a>
            <div class="auth-card">
                <div class="auth-card-core">
                    <span class="eyebrow">Setup Awal Akun</span>
                    <h1>Akunmu, makin <em>aman</em></h1>
                    <p class="auth-sub">Buat <strong>kata kunci</strong> rahasia. Kalau nanti lupa password, kata kunci ini membantumu pulih <em>tanpa bergantung email</em>. Isi alamat juga kalau mau checkout lebih cepat.</p>

                    <form method="POST" action="/geprek-geh/account/setup" id="setupForm" novalidate>
                        <?= csrf_field() ?>

                        <div class="setup-steps">
                            <div class="setup-step <?= true ? 'is-active' : '' ?>">
                                <span class="setup-step-num">01</span>
                                <span class="setup-step-txt"><strong>Kata kunci &mdash; wajib</strong><small>6&ndash;72 karakter, ingat baik-baik</small></span>
                            </div>
                        </div>

                        <div class="form-group reg-field-icon">
                            <label for="su-keyword">Kata Kunci Akun</label>
                            <div class="reg-input-wrap reg-input-pass">
                                <svg class="reg-input-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-8 8M18 5l3 3M16 7l1 1M12 6l-4 4a4 4 0 0 0-6 6l14 4 4-4a4 4 0 0 0-4-4l-4-4z" transform="rotate(90 12 12)"/></svg>
                                <input id="su-keyword" type="password" name="keyword" class="input reg-input <?= !empty($setup_errors['keyword']) ? 'is-invalid' : '' ?>" minlength="6" maxlength="72" placeholder="Bukan password, tapi frasa rahasia" autocomplete="off" autofocus value="<?= e($setup_old['keyword'] ?? '') ?>">
                                <button type="button" class="pass-toggle" data-toggle-pass="su-keyword" aria-label="Tampilkan kata kunci">
                                    <svg class="eye-open" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg class="eye-closed" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                </button>
                            </div>
                            <p class="field-hint">Bisa beda dari password. Siapa pun yang tahu kata kunci ini bisa memulihkan akunmu.</p>
                            <?php if (!empty($setup_errors['keyword'])): ?><span class="field-error"><?= e($setup_errors['keyword']) ?></span><?php endif; ?>
                        </div>

                        <div class="setup-steps">
                            <div class="setup-step">
                                <span class="setup-step-num">02</span>
                                <span class="setup-step-txt"><strong>Alamat pengiriman &mdash; opsional</strong><small>Kosongkan untuk dilewati</small></span>
                            </div>
                        </div>

                        <div class="setup-addr">
                            <div class="form-group">
                                <label for="su-name">Nama Penerima</label>
                                <input id="su-name" type="text" name="recipient_name" class="input <?= !empty($setup_errors['recipient_name']) ? 'is-invalid' : '' ?>" maxlength="100" placeholder="Nama lengkap penerima" value="<?= e($setup_old['recipient_name'] ?? '') ?>">
                                <?php if (!empty($setup_errors['recipient_name'])): ?><span class="field-error"><?= e($setup_errors['recipient_name']) ?></span><?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label for="su-phone">Telepon</label>
                                <input id="su-phone" type="tel" name="phone" class="input" maxlength="20" placeholder="08xxxxxxxxxx" value="<?= e($setup_old['phone'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label for="su-address">Alamat Lengkap</label>
                                <textarea id="su-address" name="address" class="input <?= !empty($setup_errors['address']) ? 'is-invalid' : '' ?>" rows="2" maxlength="500" placeholder="Jalan, gang, no. rumah, patokan..."><?= e($setup_old['address'] ?? '') ?></textarea>
                                <?php if (!empty($setup_errors['address'])): ?><span class="field-error"><?= e($setup_errors['address']) ?></span><?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label for="su-city">Kota / Kabupaten</label>
                                <input id="su-city" type="text" name="city" class="input" maxlength="100" placeholder="Kota" value="<?= e($setup_old['city'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label for="su-district">Kecamatan</label>
                                <input id="su-district" type="text" name="district" class="input" maxlength="100" placeholder="Kecamatan" value="<?= e($setup_old['district'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label for="su-postal">Kode Pos</label>
                                <input id="su-postal" type="text" name="postal_code" class="input <?= !empty($setup_errors['postal_code']) ? 'is-invalid' : '' ?>" maxlength="5" placeholder="12345" inputmode="numeric" value="<?= e($setup_old['postal_code'] ?? '') ?>">
                                <?php if (!empty($setup_errors['postal_code'])): ?><span class="field-error"><?= e($setup_errors['postal_code']) ?></span><?php endif; ?>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block btn-lg reg-submit" id="setupSubmit">
                            <span class="reg-submit-text">Simpan &amp; Selesai</span>
                            <span class="btn-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </span>
                            <span class="reg-submit-loading" aria-hidden="true">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                            </span>
                        </button>
                    </form>

                    <form method="POST" action="/geprek-geh/account/setup/skip" id="setupSkipForm">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-ghost btn-block" id="setupSkipBtn">Lewati — atur nanti di Akun → Keamanan</button>
                    </form>

                    <p class="auth-link">Urgent? <a href="/geprek-geh/auth/login">Ini bisa dilewati</a>.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
    document.querySelectorAll('[data-toggle-pass]').forEach(function(btn){
        btn.addEventListener('click', function(){
            var input = document.getElementById(this.dataset.togglePass);
            var isPass = input.type === 'password';
            input.type = isPass ? 'text' : 'password';
            this.classList.toggle('is-visible', isPass);
            input.focus();
        });
    });

    var form = document.getElementById('setupForm');
    var kw = document.getElementById('su-keyword');
    var fieldError = null;

    kw.addEventListener('input', function(){
        var v = this.value;
        var msg = null;
        if (v && v.length < 6) { msg = 'Kata kunci minimal 6 karakter.'; }
        else if (v.length > 72) { msg = 'Kata kunci maksimal 72 karakter.'; }
        if (!fieldError){
            fieldError = document.createElement('span');
            fieldError.className = 'field-error';
            fieldError.style.display = 'none';
            kw.closest('.form-group').appendChild(fieldError);
        }
        fieldError.style.display = msg ? 'flex' : 'none';
        fieldError.textContent = msg || '';
        kw.classList.toggle('is-invalid', !!msg);
    });

    form.addEventListener('submit', function(e){
        var msg = null;
        var v = kw.value;
        if (v.length < 6) { msg = 'Kata kunci wajib diisi, minimal 6 karakter.'; }
        else if (v.length > 72) { msg = 'Kata kunci maksimal 72 karakter.'; }
        if (msg){
            e.preventDefault();
            if (!fieldError){
                fieldError = document.createElement('span');
                fieldError.className = 'field-error';
                kw.closest('.form-group').appendChild(fieldError);
            }
            fieldError.style.display = 'flex';
            fieldError.textContent = msg;
            kw.classList.add('is-invalid');
            kw.focus();
            return;
        }
        var btn = document.getElementById('setupSubmit');
        btn.classList.add('is-loading');
        btn.disabled = true;
    });
})();
</script>