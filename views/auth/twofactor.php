<?php $page_title = 'Verifikasi 2FA'; ?>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-card-core">
            <div class="twofa-badge">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </div>
            <h1>Verifikasi 2 Langkah</h1>
            <p>Masukkan kode 6 digit dari aplikasi authenticator<?= $twofa_name ? ' untuk <strong>' . e($twofa_name) . '</strong>' : '' ?>.</p>
            <form method="POST" action="/geprek-geh/auth/2fa" id="twofa-form">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="twofa-code">Kode Verifikasi</label>
                    <input id="twofa-code" type="text" name="code" class="input twofa-code-input" inputmode="numeric" pattern="[0-9a-zA-Z]{6,8}" placeholder="______" autocomplete="one-time-code" maxlength="8" required autofocus>
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg">Verifikasi & Masuk</button>
            </form>
            <p class="auth-link">Kode tidak berhasil? Gunakan salah satu <a href="/geprek-geh/auth/login">recovery code</a> yang disimpan saat aktivasi.</p>
            <p class="auth-link"><a href="/geprek-geh/auth/login">Kembali ke halaman login</a></p>
        </div>
    </div>
</div>