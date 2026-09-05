<?php $page_title = 'Login'; ?>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-card-core">
            <h1>Login</h1>
            <p>Masuk ke akun Geprek Geh Anda</p>
            <form method="POST" action="/geprek-geh/auth/login">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="input" required placeholder="you@email.com">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="input" required placeholder="••••••">
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg">Login
                    <span class="btn-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg>
                    </span>
                </button>
            </form>
            <p class="auth-link">Belum punya akun? <a href="/geprek-geh/auth/register">Daftar di sini</a></p>
        </div>
    </div>
</div>
