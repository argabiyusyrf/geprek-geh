<?php $admin_page_title = 'Backup Database'; ?>

<div class="admin-hero">
    <div>
        <span class="admin-hero-eyebrow">Sistem</span>
        <h2 class="admin-hero-title">Backup Database</h2>
        <p class="admin-hero-sub">
            Simpan salinan database secara berkala. File tersimpan di <code>logs/backups/</code> dan bisa diunduh di sini.
        </p>
    </div>
    <div class="admin-hero-actions">
        <form method="POST" action="/admin/backup/run" style="display:inline">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-primary btn-sm">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                Backup Sekarang
            </button>
        </form>
    </div>
</div>

<div class="card">
    <div class="admin-card-head">
        <div class="admin-title min">
            <h3>Riwayat Backup <span class="text-muted">(<?= count($backups) ?>)</span></h3>
        </div>
        <span class="text-muted" style="font-size:12px;font-weight:700;">Terbaru di atas</span>
    </div>

    <?php if (empty($backups)): ?>
        <p class="text-muted" style="padding:14px 0;">Belum ada backup. Klik "Backup Sekarang" untuk membuat salinan pertama.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>File</th>
                    <th>Tanggal</th>
                    <th>Ukuran</th>
                    <th class="th-actions">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($backups as $b): ?>
                <tr>
                    <td><code class="file-name"><?= e($b['name']) ?></code></td>
                    <td><?= date('d M Y, H:i', $b['time']) ?></td>
                    <td><?= e(number_format((float) $b['size'], 1, ',', '.') . ' KB') ?></td>
                    <td class="td-actions">
                        <a class="btn btn-ghost btn-sm" href="/admin/backup/<?= e($b['name']) ?>/download" title="Unduh">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                        </a>
                        <form method="POST" action="/admin/backup/<?= e($b['name']) ?>/delete" class="inline-form" data-confirm="Hapus backup ini?">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger-ghost btn-sm" title="Hapus">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/></svg>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<div class="card" style="margin-top:18px;">
    <div class="admin-title min">
        <h3>Tips</h3>
    </div>
    <p class="text-muted" style="margin-top:10px;">
        Untuk backup otomatis rutin, pasang cron harian:
        <code>0 3 * * * cd /var/www/html && php scripts/backup.php &gt;&gt; logs/backup.log 2&gt;&amp;1</code>
    </p>
</div>