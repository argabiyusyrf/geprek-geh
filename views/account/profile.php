<?php $page_title = 'Akun Saya'; ?>

<div class="page-top">
    <header class="page-hero">
        <h1>Akun Saya</h1>
        <p class="sub">Kelola profil, keamanan, pesanan, dan alamat pengiriman dalam satu halaman.</p>
    </header>
</div>

<?php
    $tabs = [
        'overview'  => ['Overview', '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>'],
        'profil'    => ['Profil', '<circle cx="12" cy="7" r="4"/><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>'],
        'security'  => ['Keamanan', '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>'],
        'addresses' => ['Alamat', '<path d="M12 21s-7-5.3-7-11a7 7 0 0 1 14 0c0 5.7-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>'],
        'settings'  => ['Pengaturan', '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09a1.65 1.65 0 0 0 1.51-1 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33h.01a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82v.01a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>'],
    ];
    $tab = $tab ?? 'overview';
?>

<!-- ============ PILL TABS ============ -->
<div class="account-tabs" data-reveal>
    <?php foreach ($tabs as $key => [$label, $icon]): ?>
        <a href="/geprek-geh/account?tab=<?= $key ?>" class="account-tab <?= $tab === $key ? 'is-active' : '' ?>">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><?= $icon ?></svg>
            <?= $label ?>
        </a>
    <?php endforeach; ?>
</div>

<div class="account-panel">

    <?php if ($tab === 'overview'): ?>
    <?php
        $default_addr = null;
        foreach (($addresses ?? []) as $ad) {
            if ((int) $ad['is_default'] === 1) { $default_addr = $ad; break; }
        }
        $shortcuts = [
            'profil'    => ['Profil', '<circle cx="12" cy="7" r="4"/><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>'],
            'security'  => ['Keamanan', '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>'],
            'addresses' => ['Alamat', '<path d="M12 21s-7-5.3-7-11a7 7 0 0 1 14 0c0 5.7-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>'],
            'settings'  => ['Pengaturan', '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09a1.65 1.65 0 0 0 1.51-1 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33h.01a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82v.01a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>'],
        ];
    ?>
    <section class="card account-card account-card--full overview-hero" data-reveal>
        <div class="overview-hero-inner">
            <div class="overview-avatar">
                <span><?= e(strtoupper(mb_substr($user['name'], 0, 1))) ?></span>
            </div>
            <div class="overview-ident">
                <h3 class="overview-name">Halo, <?= e($user['name']) ?>!</h3>
                <p class="overview-email"><?= e($user['email']) ?></p>
                <p class="overview-meta"><?= e($user['phone'] ?: 'Belum ada nomor') ?> &middot; Bergabung sejak <?= e(date('M Y', strtotime($user['created_at']))) ?></p>
            </div>
        </div>
    </section>

    <div class="overview-stats">
        <div class="stat-card mini" data-reveal>
            <span class="stat-label">Total Pesanan</span>
            <strong><?= (int) $order_count ?></strong>
        </div>
        <div class="stat-card mini" data-reveal>
            <span class="stat-label">Total Belanja</span>
            <strong><?= rupiah($order_total) ?></strong>
        </div>
        <div class="stat-card mini" data-reveal>
            <span class="stat-label">Status Terbanyak</span>
            <strong><?= $top_status ? (format_status($top_status)[0]) : '—' ?></strong>
        </div>
    </div>

    <div class="overview-cols">
        <section class="card account-card account-card--full" data-reveal>
            <div class="account-card-head">
                <h3>Pesanan Terakhir</h3>
                <a href="/geprek-geh/orders" class="btn btn-ghost btn-sm">Lihat Semua
                    <span class="btn-icon"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg></span>
                </a>
            </div>
            <?php if (empty($recent_orders)): ?>
                <div class="account-empty">
                    <p>Belum ada pesanan. Yuk mulai belanja menu favoritmu.</p>
                    <a href="/geprek-geh/products" class="btn btn-primary btn-sm">Mulai Belanja</a>
                </div>
            <?php else: ?>
                <?php $o = $recent_orders[0];
                    [$status_label, $badge_class] = format_status($o['status']);
                ?>
                <a href="/geprek-geh/orders/<?= $o['id'] ?>" class="account-order-single">
                    <span class="invoice"><?= e($o['invoice_no']) ?></span>
                    <span class="order-date"><?= date('d M Y, H:i', strtotime($o['created_at'])) ?></span>
                    <span class="badge <?= $badge_class ?>"><?= $status_label ?></span>
                    <span class="order-total"><?= rupiah($o['grand_total']) ?></span>
                </a>
            <?php endif; ?>
        </section>

        <section class="card account-card account-card--full" data-reveal>
            <h3>Alamat Utama</h3>
            <?php if ($default_addr): ?>
                <p class="address-name"><?= e($default_addr['recipient_name']) ?></p>
                <p class="address-full"><?= e($default_addr['address']) ?></p>
                <?php $region = array_filter([$default_addr['village'], $default_addr['district'], $default_addr['city'], $default_addr['province']], fn($v) => !empty($v)); ?>
                <?php if ($region): ?><p class="address-region"><?= e(implode(', ', $region)) ?></p><?php endif; ?>
                <p class="address-link"><a href="/geprek-geh/account?tab=addresses">Kelola alamat</a></p>
            <?php else: ?>
                <div class="account-empty account-empty--compact">
                    <p>Belum ada alamat tersimpan.</p>
                    <a href="/geprek-geh/account?tab=addresses" class="btn btn-primary btn-sm">Tambah Alamat</a>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <div class="overview-shortcuts">
        <?php foreach ($shortcuts as $key => [$label, $icon]): ?>
            <a href="/geprek-geh/account?tab=<?= $key ?>" class="sc-card" data-reveal>
                <span class="sc-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><?= $icon ?></svg></span>
                <span class="sc-label"><?= $label ?></span>
                <svg class="sc-arrow" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
        <?php endforeach; ?>
    </div>

    <?php elseif ($tab === 'profil'): ?>
    <?php
        $profile_errors = $profile_errors ?? [];
        $name = $name ?? '';
        $phone = $phone ?? '';
        $address = $address ?? '';
        $completeness = 0;
        if ($name) $completeness += 1;
        if ($phone && preg_match('/^08\d{8,11}$/', $phone)) $completeness += 1;
        if ($address) $completeness += 1;
        $completeness = (int) round(($completeness / 3) * 100);
    ?>
    <div class="account-grid account-grid--profil">
        <section class="card account-card" data-reveal>
            <h3>Biodata</h3>
            <p class="account-lead">Perbarui informasi kontak yang dipakai untuk pesanan.</p>
            <form method="POST" action="/geprek-geh/account">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="pf-name">Nama Lengkap</label>
                    <input id="pf-name" type="text" name="name" class="input <?= !empty($profile_errors['name']) ? 'is-invalid' : '' ?>" value="<?= e($name) ?>" minlength="2" maxlength="100" autocomplete="name" required>
                    <?php if (!empty($profile_errors['name'])): ?><span class="field-error"><?= e($profile_errors['name']) ?></span><?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="pf-email">Email</label>
                    <input id="pf-email" type="email" class="input" value="<?= e($user['email']) ?>" readonly disabled>
                    <span class="field-hint">Email tidak dapat diubah.</span>
                </div>
                <div class="form-group">
                    <label for="pf-phone">No. Telepon</label>
                    <input id="pf-phone" type="tel" name="phone" class="input <?= !empty($profile_errors['phone']) ? 'is-invalid' : '' ?>" value="<?= e($phone) ?>" placeholder="08xxxxxxxxxx" inputmode="numeric" maxlength="20" autocomplete="tel" required>
                    <span class="field-hint">Gunakan format 08xxxxxxxxxx.</span>
                    <?php if (!empty($profile_errors['phone'])): ?><span class="field-error"><?= e($profile_errors['phone']) ?></span><?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="pf-address">Alamat</label>
                    <textarea id="pf-address" name="address" class="input <?= !empty($profile_errors['address']) ? 'is-invalid' : '' ?>" rows="3" maxlength="500" autocomplete="street-address" placeholder="Jalan, No, RT/RW, Kelurahan, Kecamatan, Kota, Kode Pos" required><?= e($address) ?></textarea>
                    <?php if (!empty($profile_errors['address'])): ?><span class="field-error"><?= e($profile_errors['address']) ?></span><?php endif; ?>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Simpan Perubahan</button>
            </form>
        </section>

        <aside class="account-side" data-reveal>
            <section class="card account-card profile-summary">
                <div class="profile-avatar"><span><?= e(strtoupper(mb_substr($user['name'], 0, 1))) ?></span></div>
                <h3 class="profile-summary-name"><?= e($user['name']) ?></h3>
                <p class="profile-summary-email"><?= e($user['email']) ?></p>
                <p class="profile-summary-join">Bergabung sejak <?= e(date('d M Y', strtotime($user['created_at']))) ?></p>

                <div class="profile-progress" aria-label="Kelengkapan profil: <?= $completeness ?> persen">
                    <div class="profile-progress-head">
                        <span>Kelengkapan Profil</span>
                        <strong><?= $completeness ?>%</strong>
                    </div>
                    <div class="profile-progress-track"><span class="profile-progress-bar" style="width: <?= $completeness ?>%"></span></div>
                    <p class="profile-progress-note"><?= $completeness === 100 ? 'Profilmu sudah lengkap.' : 'Lengkapi nomor & alamat supaya checkout lebih cepat.' ?></p>
                </div>

                <nav class="profile-quick">
                    <a href="/geprek-geh/account?tab=security">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        Keamanan & Password
                    </a>
                    <a href="/geprek-geh/account?tab=addresses">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7-5.3-7-11a7 7 0 0 1 14 0c0 5.7-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
                        Kelola Alamat
                    </a>
                    <a href="/geprek-geh/orders">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11V7a3 3 0 0 1 6 0v4"/><path d="M4 9h16l1 11H3L4 9z"/></svg>
                        Pesanan Saya
                    </a>
                </nav>
            </section>
        </aside>
    </div>

    <?php elseif ($tab === 'security'): ?>
    <?php
        $pwd_errors = $pwd_errors ?? [];
        $twofa_setup   = $twofa_setup ?? null;
        $twofa_uri     = $twofa_uri ?? '';
        $twofa_recovery = $twofa_recovery ?? null;
        $twofa_error    = $twofa_error ?? null;
        $twofa_enabled  = (int) ($user['totp_enabled'] ?? 0) === 1;
    ?>
    <div class="account-grid account-grid--profil">
        <section class="card account-card" data-reveal>
            <h3>Ubah Password</h3>
            <p class="account-lead">Ganti password secara berkala untuk melindungi akunmu.</p>
            <?php if (!empty($pwd_errors['general'])): ?><div class="alert alert-error"><?= e($pwd_errors['general']) ?></div><?php endif; ?>
            <form method="POST" action="/geprek-geh/account/password">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label>Password Lama</label>
                    <input type="password" name="current_password" class="input <?= !empty($pwd_errors['current_password']) ? 'is-invalid' : '' ?>" autocomplete="current-password" required>
                    <?php if (!empty($pwd_errors['current_password'])): ?><span class="field-error"><?= e($pwd_errors['current_password']) ?></span><?php endif; ?>
                </div>
                <div class="form-group">
                    <label>Password Baru</label>
                    <input type="password" name="new_password" class="input <?= !empty($pwd_errors['new_password']) ? 'is-invalid' : '' ?>" minlength="6" autocomplete="new-password" required>
                    <span class="field-hint">Minimal 6 karakter.</span>
                    <?php if (!empty($pwd_errors['new_password'])): ?><span class="field-error"><?= e($pwd_errors['new_password']) ?></span><?php endif; ?>
                </div>
                <div class="form-group">
                    <label>Konfirmasi Password Baru</label>
                    <input type="password" name="new_password_confirm" class="input <?= !empty($pwd_errors['new_password_confirm']) ? 'is-invalid' : '' ?>" minlength="6" autocomplete="new-password" required>
                    <?php if (!empty($pwd_errors['new_password_confirm'])): ?><span class="field-error"><?= e($pwd_errors['new_password_confirm']) ?></span><?php endif; ?>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Perbarui Password</button>
            </form>
        </section>

        <section class="card account-card twofa-card" data-reveal>
            <div class="twofa-head">
                <span class="twofa-shield">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </span>
                <div>
                    <h3>Autentikasi 2 Langkah</h3>
                    <p class="account-lead twofa-lead">Lapisan keamanan ekstra: butuh kode 6 digit dari aplikasi authenticator saat login.</p>
                </div>
                <span class="twofa-status <?= $twofa_enabled ? 'is-on' : '' ?>"><?= $twofa_enabled ? 'Aktif' : 'Nonaktif' ?></span>
            </div>

            <?php if (!empty($twofa_recovery)): ?>
                <div class="twofa-recovery">
                    <p class="twofa-recovery-title">Recovery Code — simpan baik-baik</p>
                    <p class="twofa-recovery-note">Setiap kode hanya bisa dipakai <strong>sekali</strong> untuk login atau menonaktifkan 2FA. Halaman ini hanya menampilkannya satu kali.</p>
                    <ul class="twofa-recovery-list">
                        <?php foreach ($twofa_recovery as $rc): ?><li><code><?= e($rc) ?></code></li><?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($twofa_error): ?><div class="alert alert-error"><?= e($twofa_error) ?></div><?php endif; ?>

            <?php if ($twofa_enabled): ?>
                <p class="twofa-hint">2FA aktif untuk email <strong><?= e($user['email']) ?></strong>. Siapkan aplikasi authenticator (Google Authenticator, Authy, 1Password, dll) bila pindah perangkat.</p>
                <div class="twofa-actions">
                    <form method="POST" action="/geprek-geh/account/2fa/recovery" data-reveal>
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-ghost btn-block">Ganti Recovery Code</button>
                    </form>
                </div>
                <div class="twofa-disable">
                    <p class="twofa-hint">Nonaktifkan 2FA? Masukkan kode dari authenticator atau salah satu recovery code.</p>
                    <form method="POST" action="/geprek-geh/account/2fa/disable"
                          data-confirm="Nonaktifkan autentikasi 2 langkah? Keamanan akun akan berkurang.">
                        <?= csrf_field() ?>
                        <div class="form-group">
                            <label for="twofa-dc">Kode Verifikasi</label>
                            <input id="twofa-dc" type="text" name="code" class="input twofa-code-input" inputmode="numeric" placeholder="000000 / recovery" autocomplete="one-time-code" maxlength="8" required>
                        </div>
                        <button type="submit" class="btn btn-danger btn-block">Nonaktifkan 2FA</button>
                    </form>
                </div>

            <?php elseif ($twofa_setup): ?>
                <p class="twofa-hint">1) Pindai QR di bawah dengan aplikasi authenticator, atau masukkan kunci rahasia secara manual.</p>
                <div class="twofa-qr">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=210x210&amp;margin=6&amp;qzone=1&amp;data=<?= e(rawurlencode($twofa_uri)) ?>" alt="QR code untuk 2FA" width="210" height="210" loading="lazy">
                </div>
                <div class="twofa-secret">
                    <code id="twofa-secret-text"><?= e($twofa_setup) ?></code>
                    <button type="button" class="btn btn-ghost btn-sm twofa-copy" data-copy="#twofa-secret-text">Salin</button>
                </div>
                <p class="twofa-hint twofa-hint--manual">Atau buka <code>otpauth://</code> ini di aplikasi: <span class="twofa-uri"><?= e($twofa_uri) ?></span></p>

                <p class="twofa-hint">2) Masukkan kode 6 digit yang tampil di aplikasi untuk mengaktifkan.</p>
                <form method="POST" action="/geprek-geh/account/2fa/confirm">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label for="twofa-conf">Kode Verifikasi</label>
                        <input id="twofa-conf" type="text" name="code" class="input twofa-code-input" inputmode="numeric" pattern="[0-9]{6}" placeholder="______" autocomplete="one-time-code" maxlength="6" required>
                    </div>
                    <div class="twofa-inline-actions">
                        <button type="submit" class="btn btn-primary">Aktifkan 2FA</button>
                        <button type="submit" formaction="/geprek-geh/account/2fa/cancel" class="btn btn-ghost">Batal</button>
                    </div>
                </form>

            <?php else: ?>
                <p class="twofa-hint">Melindungi akun dengan kode berubah-ubah dari aplikasi authenticator. Tanpa 2FA, siapa pun yang tahu password bisa masuk ke akunmu.</p>
                <form method="POST" action="/geprek-geh/account/2fa/setup">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-primary btn-block">Aktifkan 2FA</button>
                </form>
            <?php endif; ?>
        </section>
    </div>

    <?php elseif ($tab === 'addresses'): ?>
    <?php
        $address_errors = $address_errors ?? [];
        $a_old = $address_old ?? null;
        $edit_id = $edit_id ?? null;
        $drawer_start_open = ($a_old || $edit_id) ? true : false;
        // objek yang sedang diedit (untuk prefill drawer)
        $edit_addr = null;
        if ($edit_id) {
            foreach ($addresses as $ad) {
                if ((int) $ad['id'] === (int) $edit_id) { $edit_addr = $ad; break; }
            }
        }
    ?>
    <section class="card account-card account-card--full" data-reveal>
        <div class="account-card-head">
            <div>
                <h3>Alamat Pengiriman</h3>
                <p class="account-lead">Simpan beberapa alamat untuk pengiriman pesanan yang lebih cepat.</p>
            </div>
            <button type="button" class="btn btn-primary btn-sm" data-open-address-drawer><?php echo "Tambah Alamat"; ?>
                <span class="btn-icon"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg></span>
            </button>
        </div>

        <?php if (empty($addresses)): ?>
            <div class="account-empty">
                <p>Belum ada alamat tersimpan.</p>
                <button type="button" class="btn btn-primary btn-sm" data-open-address-drawer>Tambah Alamat Pertama</button>
            </div>
        <?php else: ?>
            <div class="address-grid">
                <?php foreach ($addresses as $i => $ad): ?>
                    <div class="address-card <?= (int) $ad['is_default'] === 1 ? 'is-default' : '' ?>" data-index="<?= $i ?>">
                        <div class="address-card-top">
                            <span class="address-label">
                                <?= e($ad['label'] ?: 'Alamat') ?>
                                <?php if ((int) $ad['is_default'] === 1): ?><span class="address-badge">Utama</span><?php endif; ?>
                            </span>
                            <span class="address-actions">
                                <form method="POST" action="/geprek-geh/account/addresses/<?= $ad['id'] ?>/edit" class="address-inline-form">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="address-btn">Edit</button>
                                </form>
                            </span>
                        </div>
                        <div class="address-card-body">
                            <p class="address-name"><?= e($ad['recipient_name']) ?></p>
                            <p class="address-full"><?= e($ad['address']) ?></p>
                            <?php $region = array_filter([$ad['village'], $ad['district'], $ad['city'], $ad['province']], fn($v) => !empty($v)); ?>
                            <?php if ($region): ?><p class="address-region"><?= e(implode(', ', $region)) ?></p><?php endif; ?>
                            <?php if (!empty($ad['postal_code'])): ?><p class="address-region">Kode Pos <?= e($ad['postal_code']) ?></p><?php endif; ?>
                            <?php if (!empty($ad['phone'])): ?><p class="address-phone"><?= e($ad['phone']) ?></p><?php endif; ?>
                        </div>
                        <div class="address-card-foot">
                            <?php if ((int) $ad['is_default'] !== 1): ?>
                                <form method="POST" action="/geprek-geh/account/addresses/<?= $ad['id'] ?>/set-default" class="address-inline-form">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="address-link">Set Utama</button>
                                </form>
                            <?php endif; ?>
                            <form method="POST" action="/geprek-geh/account/addresses/<?= $ad['id'] ?>/delete" class="address-inline-form"
                                  data-confirm="Hapus alamat ini?">
                                <?= csrf_field() ?>
                                <button type="submit" class="address-link address-link--danger">Hapus</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- ============ ADDRESS DRAWER (right side) ============ -->
    <div class="drawer-overlay" id="address-drawer-overlay" data-drawer-overlay></div>
    <aside class="drawer" id="address-drawer" data-address-drawer aria-hidden="true">
        <div class="drawer-head">
            <h4 id="drawer-title"><?= $edit_addr ? 'Edit Alamat' : 'Tambah Alamat' ?></h4>
            <button type="button" class="drawer-close" data-close-address-drawer aria-label="Tutup">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>

        <?php
            $fv = function ($k, $def = '') use ($a_old, $edit_addr) {
                if ($a_old !== null && array_key_exists($k, $a_old)) return $a_old[$k];
                if ($edit_addr) return $edit_addr[$k] ?? $def;
                return $def;
            };
            $fdef = $edit_addr ? (int) $edit_addr['is_default'] : 0;
        ?>

        <form method="POST" action="/geprek-geh/account/addresses<?= $edit_addr ? '/' . $edit_addr['id'] : '' ?>" class="drawer-body">
            <?= csrf_field() ?>

            <?php if ($a_old && !empty($address_errors)): ?>
                <div class="alert alert-error">Mohon periksa kembali isian form alamat.</div>
            <?php endif; ?>

            <div class="form-group">
                <label>Label Alamat <span class="muted-sm">(mis. Rumah, Kantor)</span></label>
                <input type="text" name="label" class="input" value="<?= e($fv('label')) ?>" placeholder="Rumah">
            </div>

            <div class="form-group">
                <label>Nama Penerima <span class="req">*</span></label>
                <input type="text" name="recipient_name" class="input <?= !empty($address_errors['recipient_name']) ? 'is-invalid' : '' ?>" value="<?= e($fv('recipient_name')) ?>" required>
                <?php if (!empty($address_errors['recipient_name'])): ?><span class="field-error"><?= e($address_errors['recipient_name']) ?></span><?php endif; ?>
            </div>

            <div class="form-group">
                <label>No. Handphone Penerima <span class="req">*</span></label>
                <input type="tel" name="phone" class="input <?= !empty($address_errors['phone']) ? 'is-invalid' : '' ?>" value="<?= e($fv('phone')) ?>" placeholder="08xxxxxxxxxx" inputmode="numeric">
                <?php if (!empty($address_errors['phone'])): ?><span class="field-error"><?= e($address_errors['phone']) ?></span><?php endif; ?>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Provinsi</label>
                    <input type="text" name="province" class="input" value="<?= e($fv('province')) ?>" placeholder="Jawa Barat">
                </div>
                <div class="form-group">
                    <label>Kota / Kabupaten</label>
                    <input type="text" name="city" class="input" value="<?= e($fv('city')) ?>" placeholder="Bandung">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Kecamatan</label>
                    <input type="text" name="district" class="input" value="<?= e($fv('district')) ?>" placeholder="Coblong">
                </div>
                <div class="form-group">
                    <label>Kelurahan</label>
                    <input type="text" name="village" class="input" value="<?= e($fv('village')) ?>" placeholder="Dago">
                </div>
            </div>

            <div class="form-group">
                <label>Kode Pos</label>
                <input type="text" name="postal_code" class="input <?= !empty($address_errors['postal_code']) ? 'is-invalid' : '' ?>" value="<?= e($fv('postal_code')) ?>" placeholder="40135" inputmode="numeric" maxlength="5">
                <?php if (!empty($address_errors['postal_code'])): ?><span class="field-error"><?= e($address_errors['postal_code']) ?></span><?php endif; ?>
            </div>

            <div class="form-group">
                <label>Detail Alamat <span class="req">*</span></label>
                <textarea name="address" class="input <?= !empty($address_errors['address']) ? 'is-invalid' : '' ?>" rows="3" placeholder="Nama jalan, nomor rumah, gedung, patokan" required><?= e($fv('address')) ?></textarea>
                <?php if (!empty($address_errors['address'])): ?><span class="field-error"><?= e($address_errors['address']) ?></span><?php endif; ?>
            </div>

            <div class="form-group">
                <label>Catatan untuk Kurir <span class="muted-sm">(opsional)</span></label>
                <textarea name="notes" class="input" rows="2" placeholder="Mis. Pagar hijau, belok kiri setelah minimarket"><?= e($fv('notes')) ?></textarea>
            </div>

            <label class="checkbox">
                <input type="checkbox" name="is_default" value="1" <?= $fdef ? 'checked' : '' ?>>
                <span>Jadikan alamat utama</span>
            </label>

            <div class="drawer-foot">
                <button type="button" class="btn btn-ghost" data-close-address-drawer>Batal</button>
                <button type="submit" class="btn btn-primary"><?= $edit_addr ? 'Simpan Perubahan' : 'Simpan Alamat' ?></button>
            </div>
        </form>
    </aside>

    <?php elseif ($tab === 'settings'): ?>
    <section class="card account-card account-card--full account-settings" data-reveal>
        <h3>Pengaturan</h3>
        <div class="account-empty">
            <p>Fitur pengaturan akun segera hadir.</p>
            <span class="ghost ghost--sm"><svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M12 1v4M12 19v4M4.2 4.2l2.8 2.8M17 17l2.8 2.8M1 12h4M19 12h4M4.2 19.8 7 17M17 7l2.8-2.8"/></svg></span>
        </div>
    </section>
    <?php endif; ?>

</div>

<?php if (($tab ?? '') === 'addresses' && $drawer_start_open): ?>
    <script>window.__ADDRESS_DRAWER_OPEN__ = true;</script>
<?php endif; ?>