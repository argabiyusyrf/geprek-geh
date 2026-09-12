<?php $admin_page_title = 'Kelola Kode Promo'; ?>

<div class="page-header page-header--wrap">
    <div>
        <h1>Kode Promo</h1>
        <p class="page-sub"><?= count($promos) ?> kode promo &middot; <?= count(array_filter($promos, fn($p) => $p['is_active'])) ?> aktif</p>
    </div>
    <button type="button" class="btn btn-primary" data-open-promo-drawer>+ Buat Kode Promo</button>
</div>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nilai</th>
                <th>Min. Order</th>
                <th>Pakai</th>
                <th>Berakhir</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($promos as $p):
                $pJson = e(json_encode([
                    'id'         => (int) $p['id'],
                    'code'       => $p['code'],
                    'type'       => $p['type'],
                    'value'      => $p['value'],
                    'min_order'  => (int) $p['min_order'],
                    'max_uses'   => $p['max_uses'] ? (int) $p['max_uses'] : null,
                    'starts_at'  => $p['starts_at'] ? date('Y-m-d\TH:i', strtotime($p['starts_at'])) : '',
                    'expires_at' => $p['expires_at'] ? date('Y-m-d\TH:i', strtotime($p['expires_at'])) : '',
                    'is_active'  => (int) $p['is_active'],
                ], JSON_UNESCAPED_UNICODE));
                $expired = $p['expires_at'] && strtotime($p['expires_at']) < time();
            ?>
            <tr>
                <td><span class="table-name promo-code"><?= e($p['code']) ?></span></td>
                <td class="stock-cell">
                    <?= $p['type'] === 'percentage' ? (float) $p['value'] . '%' : rupiah((float) $p['value']) ?>
                    <span class="table-slug"><?= $p['type'] === 'percentage' ? 'persentase' : 'potongan tetap' ?></span>
                </td>
                <td class="stock-cell"><?= $p['min_order'] ? rupiah($p['min_order']) : '—' ?></td>
                <td class="stock-cell"><?= $p['max_uses'] ? (int) $p['used_count'] . '/' . $p['max_uses'] : (int) $p['used_count'] ?></td>
                <td class="stock-cell"><?= $p['expires_at'] ? date('j M Y', strtotime($p['expires_at'])) : '—' ?></td>
                <td>
                    <span class="badge <?= $expired ? 'badge-secondary' : ($p['is_active'] ? 'badge-success' : 'badge-danger') ?>">
                        <?= $expired ? 'Kedaluwarsa' : ($p['is_active'] ? 'Aktif' : 'Nonaktif') ?>
                    </span>
                </td>
                <td>
                    <div class="table-actions">
                        <button type="button" class="btn btn-sm btn-outline" data-edit-promo='<?= $pJson ?>'>Edit</button>
                        <form method="POST" action="/geprek-geh/admin/promos/<?= $p['id'] ?>/toggle" class="inline-form">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-ghost"><?= $p['is_active'] && !$expired ? 'Nonaktifkan' : 'Aktifkan' ?></button>
                        </form>
                        <form method="POST" action="/geprek-geh/admin/promos/<?= $p['id'] ?>/delete" class="inline-form inline-form--compact" data-confirm="Hapus kode promo <?= e($p['code']) ?>?">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="drawer-overlay" id="promo-drawer-overlay" data-drawer-overlay></div>
<aside class="drawer" id="promo-drawer" data-promo-drawer aria-hidden="true" data-lenis-prevent>
    <div class="drawer-head">
        <div>
            <span class="drawer-eyebrow" id="promo-drawer-eyebrow">Kode Promo</span>
            <h4 id="promo-drawer-title">Buat Kode Promo</h4>
        </div>
        <button type="button" class="drawer-close" data-close-promo-drawer aria-label="Tutup">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
        </button>
    </div>

    <form method="POST" action="/geprek-geh/admin/promos" class="drawer-body" id="promo-form">
        <?= csrf_field() ?>

        <div class="form-group">
            <label>Kode Promo <span class="req">*</span></label>
            <input type="text" name="code" class="input promo-code-input <?= !empty($formErrors['code']) ? 'is-invalid' : '' ?>" value="<?= e(\fval($formOld, [], 'code')) ?>" placeholder="WELCOME10" required maxlength="32" style="text-transform:uppercase">
            <?php if (!empty($formErrors['code'])): ?><span class="field-error"><?= e($formErrors['code']) ?></span><?php endif; ?>
            <span class="field-hint">Huruf besar/angka tanpa spasi.</span>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Tipe Diskon</label>
                <select name="type" class="input">
                    <option value="percentage" <?= \fval($formOld, [], 'type') === 'percentage' ? 'selected' : '' ?>>Persentase (%)</option>
                    <option value="fixed" <?= \fval($formOld, [], 'type') === 'fixed' ? 'selected' : '' ?>>Nominal Tetap (Rp)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Nilai <span class="req">*</span></label>
                <input type="number" name="value" class="input <?= !empty($formErrors['value']) ? 'is-invalid' : '' ?>" step="0.01" min="0" required placeholder="10">
                <?php if (!empty($formErrors['value'])): ?><span class="field-error"><?= e($formErrors['value']) ?></span><?php endif; ?>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Min. Order (Rp)</label>
                <input type="number" name="min_order" class="input" min="0" placeholder="50000">
            </div>
            <div class="form-group">
                <label>Batas Pakai</label>
                <input type="number" name="max_uses" class="input" min="0" placeholder="100">
                <span class="field-hint">Kosongkan = tanpa batas.</span>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Mulai Berlaku</label>
                <input type="datetime-local" name="starts_at" class="input">
            </div>
            <div class="form-group">
                <label>Kedaluwarsa</label>
                <input type="datetime-local" name="expires_at" class="input <?= !empty($formErrors['expires_at']) ? 'is-invalid' : '' ?>">
                <?php if (!empty($formErrors['expires_at'])): ?><span class="field-error"><?= e($formErrors['expires_at']) ?></span><?php endif; ?>
            </div>
        </div>

        <label class="checkbox-label"><input type="checkbox" name="is_active" checked> Aktif</label>
    </form>

    <div class="drawer-foot">
        <button type="button" class="btn btn-ghost" data-close-promo-drawer>Batal</button>
        <button type="submit" form="promo-form" class="btn btn-primary" id="promo-submit">Buat Kode</button>
    </div>
</aside>

<?php
$_jsonOpts = JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG;
$__promosData = array_map(function ($p) {
    return [
        'id'         => (int) $p['id'],
        'code'       => $p['code'],
        'type'       => $p['type'],
        'value'      => (float) $p['value'],
        'min_order'  => (int) $p['min_order'],
        'max_uses'   => $p['max_uses'] ? (int) $p['max_uses'] : null,
        'starts_at'  => $p['starts_at'] ? date('Y-m-d\TH:i', strtotime($p['starts_at'])) : '',
        'expires_at' => $p['expires_at'] ? date('Y-m-d\TH:i', strtotime($p['expires_at'])) : '',
        'is_active'  => (int) $p['is_active'],
    ];
}, $promos);
$_drawerError = $formOld !== null && $formErrors !== null;
?>
<script>
window.__gehPromos = <?= json_encode($__promosData, $_jsonOpts) ?>;
window.__gehDrawerError = <?= $_drawerError ? (isset($_GET['edit']) ? json_encode(['mode' => 'edit', 'id' => (int) $_GET['edit']], $_jsonOpts) : json_encode(['mode' => 'create', 'id' => null], $_jsonOpts)) : 'null' ?>;
</script>