<?php
$admin_page_title = 'Detail Pengguna';
$self_id = \Auth::id();
$isBlocked = (int) ($user['is_blocked'] ?? 0) === 1;
$isSelf    = (int) $user['id'] === (int) $self_id;
$status_meta = [
    'pending'    => ['Menunggu', 'warn'],
    'processing' => ['Diproses', 'info'],
    'shipped'    => ['Dikirim', 'accent'],
    'delivered'  => ['Selesai', 'good'],
    'cancelled'  => ['Dibatalkan', 'bad'],
];
?>

<a href="/geprek-geh/admin/users" class="back-link">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
    Kembali ke Pengguna
</a>
<div class="breadcrumb">
    <a href="/geprek-geh/admin">Dashboard</a>
    <span>/</span>
    <a href="/geprek-geh/admin/users">Pengguna</a>
    <span>/</span>
    <span><?= e($user['name']) ?></span>
</div>

<div class="page-header page-header--wrap">
    <div>
        <h1 class="order-heading">
            <?= e($user['name']); ?>
            <span class="badge <?= $user['role'] === 'admin' ? 'badge-primary' : 'badge-secondary' ?>" style="vertical-align:middle;"><?= ucfirst($user['role']) ?></span>
            <?php if ($isBlocked): ?><span class="badge badge-danger" style="vertical-align:middle;">Diblokir</span><?php endif; ?>
        </h1>
        <p class="page-sub"><?= e($user['email']) ?> &middot; terdaftar <?= date('d M Y', strtotime($user['created_at'])) ?></p>
    </div>
    <div class="order-head-actions">
        <a href="/geprek-geh/admin/users?edit=<?= $user['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
        <?php if (!$isSelf): ?>
            <form method="POST" action="/geprek-geh/admin/users/<?= $user['id'] ?>/block" class="inline-form">
                <?= csrf_field() ?>
                <button type="submit" class="btn <?= $isBlocked ? 'btn-outline' : 'btn-primary' ?>" data-confirm="<?= $isBlocked ? 'Buka blokir akun ' . e($user['name']) . '?' : 'Blokir akun ' . e($user['name']) . '? Pengguna tidak bisa login.' ?>"><?= $isBlocked ? 'Buka Blokir' : 'Blokir' ?></button>
            </form>
            <form method="POST" action="/geprek-geh/admin/users/<?= $user['id'] ?>/delete" class="inline-form inline-form--compact" data-confirm="Hapus akun <?= e($user['name']) ?>? Seluruh pesanan, alamat, dan notifikasinya ikut terhapus. Tindakan ini tidak bisa dibatalkan.">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-danger">Hapus Akun</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card stat-card--rev">
        <span class="stat-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        </span>
        <div class="stat-number"><?= (int) ($stats['order_count'] ?? 0) ?></div>
        <div class="stat-label">Total Pesanan</div>
    </div>
    <div class="stat-card stat-card--order">
        <span class="stat-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </span>
        <div class="stat-number"><?= rupiah((int) ($stats['total_spent'] ?? 0)) ?></div>
        <div class="stat-label">Total Belanja</div>
    </div>
    <div class="stat-card stat-card--cust">
        <span class="stat-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
        </span>
        <div class="stat-number"><?= $address_count ?></div>
        <div class="stat-label">Alamat Tersimpan</div>
    </div>
    <div class="stat-card stat-card--pending">
        <span class="stat-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4 20-7Z"/></svg>
        </span>
        <div class="stat-number"><?= $notif_count ?></div>
        <div class="stat-label">Notifikasi <?= $notif_unread > 0 ? '· ' . $notif_unread . ' belum dibaca' : '' ?></div>
    </div>
</div>

<div class="admin-info-grid">
    <div class="card admin-card user-profile-card">
        <div class="admin-card-head">
            <div class="admin-title min"><h3>Profil</h3></div>
        </div>
        <div class="user-detail-list">
            <div class="user-detail-item"><span class="user-detail-label">Nama</span><span><?= e($user['name']) ?></span></div>
            <div class="user-detail-item"><span class="user-detail-label">Email</span><a href="mailto:<?= e($user['email']) ?>"><?= e($user['email']) ?></a></div>
            <div class="user-detail-item"><span class="user-detail-label">No. Handphone</span><span><?= e($user['phone'] ?: '—') ?></span></div>
            <div class="user-detail-item"><span class="user-detail-label">Role</span><span><?= ucfirst($user['role']) ?></span></div>
            <div class="user-detail-item"><span class="user-detail-label">Notifikasi Email</span><span><?= (int) ($user['notify_email'] ?? 0) === 1 ? 'Aktif' : 'Nonaktif' ?></span></div>
            <div class="user-detail-item"><span class="user-detail-label">2FA</span><span><?= (int) ($user['totp_enabled'] ?? 0) === 1 ? 'Aktif' : 'Nonaktif' ?></span></div>
            <div class="user-detail-item"><span class="user-detail-label">Kata Kunci Akun</span><span><?= !empty($user['recovery_keyword']) ? 'Tersimpan' : '—' ?></span></div>
            <div class="user-detail-item"><span class="user-detail-label">Terdaftar</span><span><?= date('d M Y H:i', strtotime($user['created_at'])) ?></span></div>
            <div class="user-detail-item"><span class="user-detail-label">Update Terakhir</span><span><?= date('d M Y H:i', strtotime($user['updated_at'])) ?></span></div>
            <?php if ($isBlocked): ?>
            <div class="user-detail-item user-detail-item--warn"><span class="user-detail-label">Diblokir Sejak</span><span><?= date('d M Y H:i', strtotime($user['blocked_at'])) ?></span></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card admin-card user-sessions-card">
        <div class="admin-card-head">
            <div class="admin-title min"><h3>Sesi Perangkat</h3></div>
            <span class="text-muted" style="font-size:12px;font-weight:700;"><?= count($sessions) ?> sesi</span>
        </div>
        <?php if ($sessions): ?>
        <div class="user-session-list">
            <?php foreach ($sessions as $s): ?>
                <div class="user-session-item">
                    <span class="user-session-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
                    </span>
                    <div class="ocell-cust-main" style="min-width:0;">
                        <span class="table-name"><?= e($s['device_type']) ?> · <?= e($s['browser']) ?></span>
                        <span class="table-slug"><?= e($s['os']) ?> &middot; IP <?= e($s['ip_address'] ?: '—') ?> &middot; <?= \time_ago($s['created_at']) ?></span>
                    </div>
                    <?php if ((int) ($s['is_current'] ?? 0) === 1): ?>
                        <span class="badge badge-success">Aktif</span>
                    <?php else: ?>
                        <span class="badge badge-secondary">Lainnya</span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
            <p class="text-muted" style="padding: 18px 22px;">Belum ada sesi tercatat.</p>
        <?php endif; ?>
    </div>
</div>

<?php if ($orders): ?>
<div class="card admin-card">
    <div class="admin-card-head">
        <div class="admin-title min"><h3>Riwayat Pesanan</h3></div>
        <span class="text-muted" style="font-size:12px;font-weight:700;"><?= (int) ($stats['order_count'] ?? 0) ?> pesanan</span>
    </div>
    <div class="table-wrap" style="border-radius:0;">
        <table class="table">
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>Pembayaran</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o):
                    [$stLabel, $stBadge] = \format_status($o['status']);
                    [$pmLabel, $pmBadge] = \format_payment_status($o['payment_status']);
                ?>
                <tr>
                    <td>
                        <a href="/geprek-geh/admin/orders/<?= $o['id'] ?>" class="ocell-invoice">
                            <span class="ocell-no"><?= e($o['invoice_no']) ?></span>
                            <span class="ocell-sub"><?= e($o['payment_method'] ?: 'transfer') ?></span>
                        </a>
                    </td>
                    <td class="stock-cell"><?= date('d M Y H:i', strtotime($o['created_at'])) ?></td>
                    <td><span class="badge <?= $stBadge ?>"><?= $stLabel ?></span></td>
                    <td><span class="badge <?= $pmBadge ?>"><?= $pmLabel ?></span></td>
                    <td class="stock-cell"><?= rupiah(grand_total($o)) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php if ($addresses): ?>
<div class="card admin-card">
    <div class="admin-card-head">
        <div class="admin-title min"><h3>Alamat Tersimpan</h3></div>
        <span class="text-muted" style="font-size:12px;font-weight:700;"><?= $address_count ?> alamat</span>
    </div>
    <div class="table-wrap" style="border-radius:0;">
        <table class="table">
            <thead>
                <tr>
                    <th>Label</th>
                    <th>Penerima</th>
                    <th>Alamat</th>
                    <th>Telepon</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($addresses as $a): ?>
                <tr>
                    <td>
                        <?= e($a['label'] ?: 'Alamat') ?>
                        <?php if ((int) ($a['is_default'] ?? 0) === 1): ?><span class="badge badge-info">Utama</span><?php endif; ?>
                    </td>
                    <td class="stock-cell">
                        <?= e($a['recipient_name']) ?>
                        <span class="table-slug"><?= e($a['province'] ?: '') ?></span>
                    </td>
                    <td>
                        <?= e($a['address']) ?>
                        <span class="table-slug"><?= trim(implode(', ', array_filter([$a['village'], $a['district'], $a['city'], $a['province']]))) . ($a['postal_code'] ? ' ' . $a['postal_code'] : '') ?></span>
                        <?php if ($a['notes']): ?><span class="table-slug">Catatan: <?= e($a['notes']) ?></span><?php endif; ?>
                    </td>
                    <td class="stock-cell"><?= e($a['phone'] ?: '—') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php if ($notifications): ?>
<div class="card admin-card">
    <div class="admin-card-head">
        <div class="admin-title min"><h3>Notifikasi Terakhir</h3></div>
        <span class="text-muted" style="font-size:12px;font-weight:700;"><?= $notif_unread ?> belum dibaca</span>
    </div>
    <div class="user-notif-list">
        <?php foreach ($notifications as $n): ?>
            <div class="user-notif-item <?= (int) ($n['is_read'] ?? 0) === 0 ? 'unread' : '' ?>">
                <span class="user-notif-dot" aria-hidden="true"></span>
                <div class="ocell-cust-main" style="min-width:0;">
                    <span class="table-name"><?= e($n['title']) ?></span>
                    <span class="table-slug"><?= e($n['message'] ?: '') ?></span>
                </div>
                <span class="table-slug"><?= \time_ago($n['created_at']) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>