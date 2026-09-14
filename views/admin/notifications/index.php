<div class="breadcrumb">
    <a href="/geprek-geh/admin">Dashboard</a>
    <span>/</span>
    <span>Notifikasi ke Pelanggan</span>
</div>

<div class="page-header page-header--wrap">
    <div>
        <h1>Notifikasi ke Pelanggan</h1>
        <p class="page-sub">Kirim pengumuman atau pesan ke semua pelanggan atau pelanggan tertentu</p>
    </div>
</div>

<div class="admin-grid-2">
    <div class="card order-card">
        <header class="order-card-head">
            <h3>Kirim Notifikasi</h3>
        </header>
        <form method="POST" action="/geprek-geh/admin/notifications" class="notif-form">
            <?= csrf_field() ?>

            <div class="form-group">
                <label>Dikirim Ke</label>
                <label class="radio-inline">
                    <input type="radio" name="target" value="all"<?= $target === 'all' ? ' checked' : '' ?> onchange="document.getElementById('notif-user-wrap').style.display = this.checked ? 'none' : 'block';">
                    Semua pelanggan
                </label>
                <label class="radio-inline">
                    <input type="radio" name="target" value="single"<?= $target === 'single' ? ' checked' : '' ?> onchange="document.getElementById('notif-user-wrap').style.display = this.checked ? 'block' : 'none';">
                    Satu pelanggan
                </label>
            </div>

            <div class="form-group" id="notif-user-wrap" style="<?= $target === 'single' ? '' : 'display:none' ?>">
                <label for="notif-user">Pelanggan</label>
                <select id="notif-user" name="user_id" class="input">
                    <option value="">— Pilih pelanggan —</option>
                    <?php foreach ($customers as $c): ?>
                        <option value="<?= (int) $c['id'] ?>"><?= e($c['name']) ?> (<?= e($c['email']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="notif-title">Judul</label>
                <input type="text" id="notif-title" name="title" class="input" maxlength="150" required placeholder="Contoh: Promo akhir pekan">
            </div>

            <div class="form-group">
                <label for="notif-message">Isi Pesan</label>
                <textarea id="notif-message" name="message" class="input" rows="3" maxlength="255" placeholder="Isi pesan yang ingin disampaikan…"></textarea>
            </div>

            <div class="form-group">
                <label for="notif-link">Tautan (opsional)</label>
                <input type="text" id="notif-link" name="link" class="input" maxlength="255" placeholder="Contoh: /products/paket-hemat">
                <span class="field-hint">Pelanggan bisa mengetuk notifikasi untuk membuka halaman ini.</span>
            </div>

            <button type="submit" class="btn btn-primary">Kirim Notifikasi</button>
        </form>
    </div>

    <div class="card order-card">
        <header class="order-card-head">
            <h3>Riwayat Notifikasi Admin</h3>
        </header>
        <?php if (empty($recent)): ?>
            <div class="empty-state empty-state--compact">
                <span class="ghost"><svg style="width:4.5rem;height:4.5rem" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg></span>
                <h3>Belum ada notifikasi</h3>
                <p>Notifikasi yang dikirim akan muncul di sini.</p>
            </div>
        <?php else: ?>
        <div class="notif-history">
            <?php foreach ($recent as $n): ?>
                <div class="notif-row">
                    <div class="notif-row-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
                    </div>
                    <div class="notif-row-body">
                        <div class="notif-row-title"><?= e($n['title']) ?></div>
                        <?php if ($n['message'] !== null && $n['message'] !== ''): ?>
                            <div class="notif-row-msg"><?= e($n['message']) ?></div>
                        <?php endif; ?>
                        <div class="notif-row-meta">
                            Kepada: <?= e($n['user_name']) ?>
                            <?php if ($n['link'] !== null && $n['link'] !== ''): ?>
                                · <a href="<?= e($n['link']) ?>"><?= e($n['link']) ?></a>
                            <?php endif; ?>
                            · <?= e(date('d M Y H:i', strtotime($n['created_at']))) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>