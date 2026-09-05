<?php $page_title = 'Daftar'; ?>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-card-core">
            <h1>Daftar Akun</h1>
            <p>Buat akun baru untuk mulai pesan</p>
            <form method="POST" action="/geprek-geh/auth/register">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="name" class="input" required placeholder="Nama Anda">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="input" required placeholder="you@email.com">
                </div>
                <div class="form-group">
                    <label>No. Telepon</label>
                    <input type="text" name="phone" class="input" placeholder="08xxx">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="input" required minlength="6" placeholder="Minimal 6 karakter">
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg">Daftar
                    <span class="btn-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>
                    </span>
                </button>
            </form>
            <p class="auth-link">Sudah punya akun? <a href="/geprek-geh/auth/login">Login</a></p>
        </div>
    </div>
</div>
