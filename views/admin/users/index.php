<?php $admin_page_title = 'Kelola Pengguna'; ?>

<div class="page-header page-header--wrap">
    <div>
        <h1>Pengguna</h1>
        <p class="page-sub"><?= count($users) ?> pengguna &middot; <?= count(array_filter($users, fn($u) => $u['role'] === 'admin')) ?> admin</p>
    </div>
    <button type="button" class="btn btn-primary" data-open-user-drawer>+ Tambah Pengguna</button>
</div>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Kontak</th>
                <th>Role</th>
                <th>Pesanan</th>
                <th>Terdaftar</th>
                <th>Notifikasi</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u):
                $uJson = e(json_encode([
                    'id'           => (int) $u['id'],
                    'name'         => $u['name'],
                    'email'        => $u['email'],
                    'phone'        => $u['phone'] ?? '',
                    'role'         => $u['role'],
                    'notify_email' => (int) ($u['notify_email'] ?? 0),
                ], JSON_UNESCAPED_UNICODE));
            ?>
            <tr>
                <td>
                    <span class="table-name"><?= e($u['name']) ?></span>
                    <span class="table-slug"><?= e($u['email']) ?></span>
                </td>
                <td class="stock-cell"><?= e($u['phone'] ?: '—') ?></td>
                <td><span class="badge <?= $u['role'] === 'admin' ? 'badge-primary' : 'badge-secondary' ?>"><?= ucfirst($u['role']) ?></span></td>
                <td class="stock-cell"><?= (int) ($u['order_count'] ?? 0) ?></td>
                <td class="stock-cell"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                <td>
                    <?php if ((int) ($u['notify_email'] ?? 0) === 1): ?>
                        <span class="badge badge-info">Email</span>
                    <?php else: ?>
                        <span class="table-slug">—</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="table-actions">
                        <button type="button" class="btn btn-sm btn-outline" data-edit-user='<?= $uJson ?>'>Edit</button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

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
$_drawerError = $formOld !== null && $formErrors !== null;
?>
<script>
window.__gehUsers = <?= json_encode($__usersData, $_jsonOpts) ?>;
window.__gehDrawerError = <?= $_drawerError ? (isset($_GET['edit']) ? json_encode(['mode' => 'edit', 'id' => (int) $_GET['edit']], $_jsonOpts) : json_encode(['mode' => 'create', 'id' => null], $_jsonOpts)) : 'null' ?>;
</script>