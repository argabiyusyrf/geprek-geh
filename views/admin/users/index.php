<?php
$admin_page_title = 'Kelola Pengguna';
$sort_label = ['terbaru' => 'Terbaru', 'terlama' => 'Terlama', 'nama' => 'A–Z', 'email' => 'Email', 'role' => 'Role'][$sort] ?? 'Terbaru';
$self_id = \Auth::id();

$extra_q = [];
if ($q !== '') $extra_q['q'] = $q;
if ($role !== '') $extra_q['role'] = $role;
if ($blocked !== '') $extra_q['blocked'] = $blocked;
if ($sort !== 'terbaru') $extra_q['sort'] = $sort;
if ($per !== 15) $extra_q['per'] = $per;

$clear_params = $extra_q;
unset($clear_params['q']);
$search_clear_href = '/geprek-geh/admin/users' . ($clear_params ? '?' . http_build_query($clear_params) : '');
$reset_href = '/geprek-geh/admin/users';

$page_window = [];
if ($total_pages <= 7) {
    $page_window = range(1, $total_pages);
} else {
    $page_window[] = 1;
    $ws = max(2, $page - 2);
    $we = min($total_pages - 1, $page + 2);
    if ($ws > 2) $page_window[] = null;
    for ($i = $ws; $i <= $we; $i++) $page_window[] = $i;
    if ($we < $total_pages - 1) $page_window[] = null;
    $page_window[] = $total_pages;
}
$qp = $extra_q;
$page_href = function (int $p) use ($qp) {
    return '/geprek-geh/admin/users?' . http_build_query(['page' => $p] + $qp);
};

$role_label = ['admin' => 'Admin', 'customer' => 'Pelanggan'][$role] ?? '';
$blocked_label = $blocked === '1' ? 'Diblokir' : ($blocked === '0' ? 'Aktif' : '');
$filter_bits = array_filter([$role_label, $blocked_label]);
?>

<div class="page-header page-header--wrap">
    <div>
        <h1>Pengguna</h1>
        <p class="page-sub"><?= $kpis['total'] ?> pengguna &middot; <?= $kpis['customer_count'] ?> pelanggan &middot; <?= $kpis['blocked_count'] ?> diblokir</p>
    </div>
    <button type="button" class="btn btn-primary" data-open-user-drawer>+ Tambah Pengguna</button>
</div>

<div class="stats-grid">
    <div class="stat-card stat-card--rev">
        <span class="stat-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </span>
        <div class="stat-number"><?= $kpis['total'] ?></div>
        <div class="stat-label">Total Pengguna</div>
    </div>
    <div class="stat-card stat-card--cust">
        <span class="stat-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </span>
        <div class="stat-number"><?= $kpis['customer_count'] ?></div>
        <div class="stat-label">Pelanggan</div>
    </div>
    <div class="stat-card stat-card--order">
        <span class="stat-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </span>
        <div class="stat-number"><?= $kpis['admin_count'] ?></div>
        <div class="stat-label">Admin</div>
    </div>
    <div class="stat-card stat-card--pending">
        <span class="stat-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg>
        </span>
        <div class="stat-number"><?= $kpis['blocked_count'] ?></div>
        <div class="stat-label">Diblokir</div>
    </div>
</div>

<div class="menu-filters admin-orders-filters">
    <form method="GET" action="/geprek-geh/admin/users" class="menu-toolbar">
        <div class="menu-filter-row">
            <?php if ($role !== ''): ?><input type="hidden" name="role" value="<?= e($role) ?>"><?php endif; ?>
            <?php if ($blocked !== ''): ?><input type="hidden" name="blocked" value="<?= e($blocked) ?>"><?php endif; ?>
            <?php if ($per !== 15): ?><input type="hidden" name="per" value="<?= e($per) ?>"><?php endif; ?>

            <div class="menu-toolbar-top">
                <div class="menu-search">
                    <svg class="menu-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                    <input type="search" name="q" placeholder="Cari nama, email, atau telepon…" value="<?= e($q) ?>" class="menu-search-input" autocomplete="off" aria-label="Cari pengguna">
                    <?php if ($q !== ''): ?>
                        <a href="<?= e($search_clear_href) ?>" class="menu-search-clear" aria-label="Bersihkan pencarian">&times;</a>
                    <?php endif; ?>
                </div>

                <span class="menu-sort">
                    <span class="menu-sort-label">Urutkan</span>
                    <span class="menu-sort-box menu-dropdown" data-dropdown data-form-submit>
                        <button type="button" class="menu-dropdown-trigger" data-dropdown-trigger aria-haspopup="listbox" aria-expanded="false">
                            <span data-dropdown-label><?= e($sort_label) ?></span>
                            <svg class="menu-sort-chev menu-sort-chev--js" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                        </button>
                        <div class="menu-dropdown-menu" data-dropdown-menu role="listbox">
                            <button type="button" class="menu-dropdown-item" data-value="terbaru" role="option">Terbaru</button>
                            <button type="button" class="menu-dropdown-item" data-value="terlama" role="option">Terlama</button>
                            <button type="button" class="menu-dropdown-item" data-value="nama" role="option">A–Z</button>
                            <button type="button" class="menu-dropdown-item" data-value="email" role="option">Email</button>
                            <button type="button" class="menu-dropdown-item" data-value="role" role="option">Role</button>
                        </div>
                        <select name="sort" class="menu-sort-select menu-sort-native" data-dropdown-select onchange="this.form.submit()" aria-label="Urutkan pengguna">
                            <option value="terbaru"<?= $sort === 'terbaru' ? ' selected' : '' ?>>Terbaru</option>
                            <option value="terlama"<?= $sort === 'terlama' ? ' selected' : '' ?>>Terlama</option>
                            <option value="nama"<?= $sort === 'nama' ? ' selected' : '' ?>>A–Z</option>
                            <option value="email"<?= $sort === 'email' ? ' selected' : '' ?>>Email</option>
                            <option value="role"<?= $sort === 'role' ? ' selected' : '' ?>>Role</option>
                        </select>
                        <svg class="menu-sort-chev menu-sort-chev--native" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                    </span>
                </span>

                <span class="menu-sort">
                    <span class="menu-sort-label">Role</span>
                    <span class="menu-sort-box">
                        <select name="role" class="menu-sort-select" onchange="this.form.submit()" aria-label="Filter role">
                            <option value=""<?= $role === '' ? ' selected' : '' ?>>Semua</option>
                            <option value="customer"<?= $role === 'customer' ? ' selected' : '' ?>>Pelanggan</option>
                            <option value="admin"<?= $role === 'admin' ? ' selected' : '' ?>>Admin</option>
                        </select>
                        <svg class="menu-sort-chev" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:block"><path d="M6 9l6 6 6-6"/></svg>
                    </span>
                </span>

                <span class="menu-sort">
                    <span class="menu-sort-label">Status</span>
                    <span class="menu-sort-box">
                        <select name="blocked" class="menu-sort-select" onchange="this.form.submit()" aria-label="Filter status">
                            <option value=""<?= $blocked === '' ? ' selected' : '' ?>>Semua</option>
                            <option value="0"<?= $blocked === '0' ? ' selected' : '' ?>>Aktif</option>
                            <option value="1"<?= $blocked === '1' ? ' selected' : '' ?>>Diblokir</option>
                        </select>
                        <svg class="menu-sort-chev" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:block"><path d="M6 9l6 6 6-6"/></svg>
                    </span>
                </span>
            </div>
        </div>
    </form>
</div>

<?php if ($q !== '' || $role !== '' || $blocked !== ''): ?>
<div class="menu-results">
    <span class="menu-results-text">
        <strong><?= $filtered_total ?></strong> pengguna
        <?php if ($q !== ''): ?>untuk "<?= e($q) ?>";<?php endif; ?>
        <?php if ($filter_bits): ?><strong><?= implode(' · ', $filter_bits) ?></strong>;<?php endif; ?>
        <a href="<?= e($reset_href) ?>" class="menu-results-reset">Reset</a>
    </span>
</div>
<?php endif; ?>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Kontak</th>
                <th>Role</th>
                <th>Pesanan</th>
                <th>Terdaftar</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$users): ?>
                <tr>
                    <td colspan="7">
                        <div class="empty-state empty-state--compact">
                            <span class="ghost">👤</span>
                            <h3>Tidak ada pengguna</h3>
                            <p><?= $q !== '' || $role !== '' || $blocked !== '' ? 'Coba ubah kata kunci atau filter.' : 'Belum ada pengguna terdaftar.' ?></p>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
            <?php foreach ($users as $u):
                $uJson = e(json_encode([
                    'id'           => (int) $u['id'],
                    'name'         => $u['name'],
                    'email'        => $u['email'],
                    'phone'        => $u['phone'] ?? '',
                    'role'         => $u['role'],
                    'notify_email' => (int) ($u['notify_email'] ?? 0),
                ], JSON_UNESCAPED_UNICODE));
                $isBlocked = (int) ($u['is_blocked'] ?? 0) === 1;
                $isSelf    = (int) $u['id'] === (int) $self_id;
            ?>
            <tr class="<?= $isBlocked ? 'row-blocked' : '' ?>">
                <td>
                    <span class="table-name"><?= e($u['name']) ?></span>
                    <span class="table-slug"><?= e($u['email']) ?></span>
                </td>
                <td class="stock-cell"><?= e($u['phone'] ?: '—') ?></td>
                <td><span class="badge <?= $u['role'] === 'admin' ? 'badge-primary' : 'badge-secondary' ?>"><?= ucfirst($u['role']) ?></span></td>
                <td class="stock-cell"><?= (int) ($u['order_count'] ?? 0) ?></td>
                <td class="stock-cell"><?= date('d M Y', strtotime($u['created_at'])) ?>
                    <?php if ($isBlocked && $u['blocked_at']): ?><span class="table-slug">diblokir <?= date('d M Y', strtotime($u['blocked_at'])) ?></span><?php endif; ?>
                </td>
                <td>
                    <?php if ($isBlocked): ?>
                        <span class="badge badge-danger">Diblokir</span>
                    <?php else: ?>
                        <span class="badge badge-success">Aktif</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="table-actions">
                        <a href="/geprek-geh/admin/users/<?= $u['id'] ?>" class="btn btn-sm btn-outline">Detail</a>
                        <?php if (!$isSelf): ?>
                            <button type="button" class="btn btn-sm btn-ghost" data-edit-user='<?= $uJson ?>'>Edit</button>
                            <form method="POST" action="/geprek-geh/admin/users/<?= $u['id'] ?>/block" class="inline-form">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm <?= $isBlocked ? 'btn-outline' : 'btn-ghost' ?>" data-confirm="<?= $isBlocked ? 'Buka blokir akun ' . e($u['name']) . '?' : 'Blokir akun ' . e($u['name']) . '? Pengguna tidak bisa login.' ?>"><?= $isBlocked ? 'Buka' : 'Blokir' ?></button>
                            </form>
                            <form method="POST" action="/geprek-geh/admin/users/<?= $u['id'] ?>/delete" class="inline-form inline-form--compact" data-confirm="Hapus akun <?= e($u['name']) ?>? Seluruh pesanan, alamat, dan notifikasinya ikut terhapus.">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        <?php else: ?>
                            <span class="table-slug">Anda</span>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($users): ?>
<nav class="menu-pagination menu-pagination--orders" aria-label="Navigasi halaman">
    <?php if ($total_pages > 1): ?>
        <div class="menu-pagination-pages">
            <?php if ($page > 1): ?>
                <a href="<?= e($page_href($page - 1)) ?>" class="menu-page-btn" aria-label="Halaman sebelumnya">&laquo;</a>
            <?php endif; ?>
            <?php foreach ($page_window as $pw): ?>
                <?php if ($pw === null): ?>
                    <span class="menu-page-dots" aria-hidden="true">&hellip;</span>
                <?php else: ?>
                    <a href="<?= e($page_href($pw)) ?>" class="menu-page-btn <?= $pw === $page ? 'active' : '' ?>"><?= $pw ?></a>
                <?php endif; ?>
            <?php endforeach; ?>
            <?php if ($page < $total_pages): ?>
                <a href="<?= e($page_href($page + 1)) ?>" class="menu-page-btn" aria-label="Halaman berikutnya">&raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <form method="GET" action="/geprek-geh/admin/users" class="menu-perpage">
        <?php if ($role !== ''): ?><input type="hidden" name="role" value="<?= e($role) ?>"><?php endif; ?>
        <?php if ($blocked !== ''): ?><input type="hidden" name="blocked" value="<?= e($blocked) ?>"><?php endif; ?>
        <?php if ($q !== ''): ?><input type="hidden" name="q" value="<?= e($q) ?>"><?php endif; ?>
        <?php if ($sort !== 'terbaru'): ?><input type="hidden" name="sort" value="<?= e($sort) ?>"><?php endif; ?>
        <label for="users-per-page" class="menu-perpage-label">Baris per halaman</label>
        <select id="users-per-page" name="per" class="menu-perpage-select" onchange="this.form.submit()" aria-label="Baris per halaman">
            <option value="10" <?= $per === 10 ? 'selected' : '' ?>>10</option>
            <option value="15" <?= $per === 15 ? 'selected' : '' ?>>15</option>
            <option value="25" <?= $per === 25 ? 'selected' : '' ?>>25</option>
            <option value="50" <?= $per === 50 ? 'selected' : '' ?>>50</option>
        </select>
    </form>
</nav>
<?php endif; ?>

<div class="drawer-overlay" id="user-drawer-overlay" data-drawer-overlay></div>
<aside class="drawer" id="user-drawer" data-user-drawer aria-hidden="true" data-lenis-prevent>
    <div class="drawer-head">
        <div>
            <span class="drawer-eyebrow" id="user-drawer-eyebrow">Pengguna</span>
            <h4 id="user-drawer-title">Tambah Pengguna</h4>
        </div>
        <button type="button" class="drawer-close" data-close-user-drawer aria-label="Tutup">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
        </button>
    </div>

    <form method="POST" action="/geprek-geh/admin/users" class="drawer-body" id="user-form">
        <?= csrf_field() ?>

        <div class="form-group">
            <label>Nama Lengkap <span class="req">*</span></label>
            <input type="text" name="name" class="input <?= !empty($formErrors['name']) ? 'is-invalid' : '' ?>" value="<?= e(\fval($formOld, [], 'name')) ?>" required>
            <?php if (!empty($formErrors['name'])): ?><span class="field-error"><?= e($formErrors['name']) ?></span><?php endif; ?>
        </div>

        <div class="form-group">
            <label>Email <span class="req">*</span></label>
            <input type="email" name="email" class="input <?= !empty($formErrors['email']) ? 'is-invalid' : '' ?>" value="<?= e(\fval($formOld, [], 'email')) ?>" required>
            <?php if (!empty($formErrors['email'])): ?><span class="field-error"><?= e($formErrors['email']) ?></span><?php endif; ?>
        </div>

        <div class="form-group">
            <label>No. Handphone</label>
            <input type="tel" name="phone" class="input <?= !empty($formErrors['phone']) ? 'is-invalid' : '' ?>" value="<?= e(\fval($formOld, [], 'phone')) ?>" placeholder="0812xxxx xxxx" inputmode="numeric">
            <?php if (!empty($formErrors['phone'])): ?><span class="field-error"><?= e($formErrors['phone']) ?></span><?php endif; ?>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Role</label>
                <select name="role" class="input">
                    <option value="customer" <?= \fval($formOld, [], 'role') !== 'admin' ? 'selected' : '' ?>>Pelanggan</option>
                    <option value="admin" <?= \fval($formOld, [], 'role') === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>
            <div class="form-group">
                <label>Password <span class="req" id="user-pass-required">*</span></label>
                <input type="password" name="password" class="input <?= !empty($formErrors['password']) ? 'is-invalid' : '' ?>" autocomplete="new-password" placeholder="Min. 8 karakter">
                <?php if (!empty($formErrors['password'])): ?><span class="field-error"><?= e($formErrors['password']) ?></span><?php endif; ?>
                <span class="field-hint" id="user-pass-hint">Kosongkan pada edit agar password tidak berubah.</span>
            </div>
        </div>

        <label class="checkbox-label"><input type="checkbox" name="notify_email"> Notifikasi email pesanan</label>
    </form>

    <div class="drawer-foot">
        <button type="button" class="btn btn-ghost" data-close-user-drawer>Batal</button>
        <button type="submit" form="user-form" class="btn btn-primary" id="user-submit">Tambah Pengguna</button>
    </div>
</aside>

<?php
$_jsonOpts = JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG;
$__usersData = array_map(function ($u) {
    return [
        'id'           => (int) $u['id'],
        'name'         => $u['name'],
        'email'        => $u['email'],
        'phone'        => $u['phone'] ?? '',
        'role'         => $u['role'],
        'notify_email' => (int) ($u['notify_email'] ?? 0),
    ];
}, $users);
if ($editTargetUser) {
    $__usersData[] = [
        'id'           => (int) $editTargetUser['id'],
        'name'         => $editTargetUser['name'],
        'email'        => $editTargetUser['email'],
        'phone'        => $editTargetUser['phone'] ?? '',
        'role'         => $editTargetUser['role'],
        'notify_email' => (int) ($editTargetUser['notify_email'] ?? 0),
    ];
}
$_drawerError = $formOld !== null && $formErrors !== null;
?>
<script>
window.__gehUsers = <?= json_encode($__usersData, $_jsonOpts) ?>;
window.__gehDrawerError = <?= $_drawerError ? (isset($_GET['edit']) ? json_encode(['mode' => 'edit', 'id' => (int) $_GET['edit']], $_jsonOpts) : json_encode(['mode' => 'create', 'id' => null], $_jsonOpts)) : 'null' ?>;
</script>