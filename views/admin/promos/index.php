<?php $admin_page_title = 'Kelola Kode Promo'; ?>

<div class="page-header">
    <h1>Kode Promo</h1>
</div>

<?php if (flash('success')): ?>
<div class="alert alert-success"><?= e(flash('success')) ?></div>
<?php endif; ?>
<?php if (flash('error')): ?>
<div class="alert alert-error"><?= e(flash('error')) ?></div>
<?php endif; ?>

<div class="admin-grid-2">
    <div class="card">
        <h3>Buat Kode Promo Baru</h3>
        <form method="POST" action="/geprek-geh/admin/promos">
            <?= csrf_field() ?>
            <div class="form-group">
                <label>Kode</label>
                <input type="text" name="code" class="input promo-code-input" placeholder="WELCOME10" required maxlength="32">
            </div>
            <div class="form-group">
                <label>Tipe Diskon</label>
                <select name="type" class="input">
                    <option value="percentage">Persentase (%)</option>
                    <option value="fixed">Nominal Tetap (Rp)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Nilai</label>
                <input type="number" name="value" class="input" step="0.01" min="1" required placeholder="10">
            </div>
            <div class="form-group">
                <label>Min. Order (Rp)</label>
                <input type="number" name="min_order" class="input" min="0" placeholder="50000">
            </div>
            <div class="form-group">
                <label>Batas Pakai (kosongkan = tak terbatas)</label>
                <input type="number" name="max_uses" class="input" min="0" placeholder="100">
            </div>
            <div class="form-group">
                <label>Mulai Berlaku</label>
                <input type="datetime-local" name="starts_at" class="input">
            </div>
            <div class="form-group">
                <label>Kedaluwarsa</label>
                <input type="datetime-local" name="expires_at" class="input">
            </div>
            <button type="submit" class="btn btn-primary">✕ Buat Kode</button>
        </form>
    </div>

    <div class="card">
        <h3>Daftar Kode Promo</h3>
        <?php if (empty($promos)): ?>
            <p class="text-muted">Belum ada kode promo.</p>
        <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Tipe</th>
                    <th>Nilai</th>
                    <th>Min. Order</th>
                    <th>Pakai</th>
                    <th>Aktif</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($promos as $p): ?>
                <tr>
                    <td><strong><?= e($p['code']) ?></strong></td>
                    <td><?= $p['type'] === 'percentage' ? '%' : 'Rp' ?></td>
                    <td><?= $p['type'] === 'percentage' ? $p['value'] . '%' : rupiah($p['value']) ?></td>
                    <td><?= $p['min_order'] ? rupiah($p['min_order']) : '—' ?></td>
                    <td><?= $p['max_uses'] ? $p['used_count'] . '/' . $p['max_uses'] : $p['used_count'] ?></td>
                    <td>
                        <form method="POST" action="/geprek-geh/admin/promos/<?= $p['id'] ?>/toggle" class="inline-form">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-ghost btn-xs <?= $p['is_active'] ? 'btn-success' : 'btn-danger' ?>">
                                <?= $p['is_active'] ? 'Aktif' : 'Non-aktif' ?>
                            </button>
                        </form>
                    </td>
                    <td>
                        <form method="POST" action="/geprek-geh/admin/promos/<?= $p['id'] ?>/delete" class="inline-form" data-confirm="Hapus kode promo ini?">
                            <?= csrf_field() ?>
                            <button class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
