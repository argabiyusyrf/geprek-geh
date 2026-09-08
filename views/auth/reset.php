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

                        <form method="POST" action="/geprek-geh/auth/reset">
                            <?= csrf_field() ?>
                            <input type="hidden" name="selector" value="<?= e($selector) ?>">
                            <input type="hidden" name="token" value="<?= e($token) ?>">

                            <div class="form-group">
                                <label for="rp-pass">Password Baru</label>
                                <input id="rp-pass" type="password" name="password" class="input" required minlength="6" placeholder="Minimal 6 karakter" autocomplete="new-password" autofocus>
                            </div>
                            <div class="form-group">
                                <label for="rp-confirm">Konfirmasi Password</label>
                                <input id="rp-confirm" type="password" name="password_confirm" class="input" required minlength="6" autocomplete="new-password" placeholder="Ketik ulang">
                            </div>
                            <button type="submit" class="btn btn-primary btn-block btn-lg">Simpan Password
                                <span class="btn-icon">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
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