<?php
$admin_page_title = 'Metode Pembayaran';
$bankCount = 0;
$ewalletCount = 0;
$inactiveCount = 0;
foreach ($methods as $m) {
    if (!$m['is_active']) { $inactiveCount++; continue; }
    if ($m['type'] === 'bank') $bankCount++;
    elseif ($m['type'] === 'ewallet') $ewalletCount++;
}
$typeLabel = ['bank' => 'Transfer Bank', 'ewallet' => 'E-Wallet', 'cod' => 'COD'];
$typeBadge = ['bank' => 'badge-info', 'ewallet' => 'badge-warning', 'cod' => 'badge-secondary'];
$typeIcon = [
    'bank' => '<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20M6 15h4"/>',
    'ewallet' => '<rect x="3" y="7" width="18" height="12" rx="3"/><path d="M7 10h8M15 14h2"/>',
    'cod' => '<rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="3"/>',
];
?>

<div class="breadcrumb">
    <a href="/geprek-geh/admin">Dashboard</a>
    <span>/</span>
    <span>Metode Pembayaran</span>
</div>

<div class="page-header page-header--wrap">
    <div>
        <h1>Metode Pembayaran</h1>
        <p class="page-sub">Kelola banyak rekening bank &amp; e-wallet. Semua metode aktif otomatis tampil di halaman checkout.</p>
    </div>
    <button type="button" class="btn btn-primary" data-pm-add>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
        Tambah Metode
    </button>
</div>

<div class="stats-grid">
    <div class="stat-card stat-card--rev">
        <span class="stat-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20M6 15h4"/></svg></span>
        <div class="stat-number"><?= $bankCount ?></div>
        <div class="stat-label">Bank Aktif</div>
    </div>
    <div class="stat-card stat-card--pending">
        <span class="stat-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="7" width="18" height="12" rx="3"/><path d="M7 10h8M15 14h2"/></svg></span>
        <div class="stat-number"><?= $ewalletCount ?></div>
        <div class="stat-label">E-Wallet Aktif</div>
    </div>
    <div class="stat-card stat-card--order">
        <span class="stat-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M2 6h20l-2 5H4z"/><path d="M4 11h16v8a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1z"/></svg></span>
        <div class="stat-number"><?= count($methods) ?></div>
        <div class="stat-label">Total Metode</div>
    </div>
    <div class="stat-card stat-card--cust">
        <span class="stat-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M6 8h4v9M15 17V11M10 17V5M15 17h3"/></svg></span>
        <div class="stat-number"><?= $inactiveCount ?></div>
        <div class="stat-label">Nonaktif</div>
    </div>
</div>

<div class="card order-card pm-add-card" data-pm-addcard hidden>
    <header class="order-card-head">
        <h3>Tambah Metode Pembayaran</h3>
        <button type="button" class="btn btn-sm btn-ghost" data-pm-add-close>Tutup</button>
    </header>
    <form method="POST" action="/geprek-geh/admin/payments" class="pm-form">
        <?= csrf_field() ?>
        <div class="form-row">
            <div class="form-group">
                <label>Tipe Pembayaran *</label>
                <div class="pm-type-row">
                    <label class="pm-type-opt">
                        <input type="radio" name="type" value="bank" checked>
                        <span>Transfer Bank</span>
                    </label>
                    <label class="pm-type-opt">
                        <input type="radio" name="type" value="ewallet">
                        <span>E-Wallet</span>
                    </label>
                </div>
            </div>
        </div>
        <div class="form-row pm-3col">
            <div class="form-group">
                <label>Nama <?= htmlspecialchars('Bank / E-Wallet') ?> *</label>
                <input type="text" name="name" class="input" placeholder="cth. BCA, GoPay" required>
            </div>
            <div class="form-group">
                <label>Nomor Rekening / E-Wallet *</label>
                <input type="text" name="number" class="input" placeholder="1234567890 / 08xxxxxxxxxx" required>
            </div>
            <div class="form-group">
                <label>Atas Nama *</label>
                <input type="text" name="holder" class="input" placeholder="GEPREK GEH" required>
            </div>
        </div>
        <div class="form-group">
            <label>Keterangan Tampil <span class="label-optional">(opsional)</span></label>
            <input type="text" name="description" class="input" placeholder="cth. Verifikasi manual oleh admin 1x24 jam" maxlength="255">
        </div>
        <div class="settings-actions">
            <button type="submit" class="btn btn-primary">Simpan Metode</button>
        </div>
    </form>
</div>

<?php if (empty($methods)): ?>
    <div class="card order-card">
        <div class="empty-state empty-state--compact">
            <span class="ghost"><svg style="width:4.5rem;height:4.5rem" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20M6 15h4"/></svg></span>
            <h3>Belum ada metode pembayaran</h3>
            <p>Tambahkan minimal satu rekening bank atau e-wallet agar pelanggan bisa membayar.</p>
        </div>
    </div>
<?php else: ?>
<?php
    $grouped = [
        'bank'     => ['Transfer Bank', []],
        'ewallet'  => ['E-Wallet', []],
        'inactive' => ['Nonaktif', []],
    ];
    foreach ($methods as $m) {
        if (!$m['is_active']) { $grouped['inactive'][1][] = $m; continue; }
        $grouped[$m['type']][1][] = $m;
    }
?>
<?php foreach ($grouped as $gkey => [$gtitle, $gitems]): ?>
    <?php if (empty($gitems)) continue; ?>
    <div class="pm-group">
        <div class="pm-group-head">
            <h3><?= e($gtitle) ?></h3>
            <span class="count-pill"><?= count($gitems) ?></span>
        </div>
        <div class="pm-list" id="pm-list-<?= $gkey ?>">
        <?php foreach ($gitems as $m): ?>
        <div class="card order-card pm-card" id="m-<?= (int) $m['id'] ?>">
            <div class="pm-card-top">
                <div class="pm-ident">
                    <span class="pm-icon pm-icon--<?= $m['type'] ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><?= $typeIcon[$m['type']] ?></svg>
                    </span>
                    <div>
                        <div class="pm-name">
                            <?= e($m['name']) ?>
                            <span class="badge <?= $typeBadge[$m['type']] ?>"><?= $typeLabel[$m['type']] ?></span>
                        </div>
                        <div class="pm-detail"><?= e($m['number']) ?>&nbsp;&bull;&nbsp;a.n. <strong><?= e($m['holder']) ?></strong></div>
                        <?php if (!empty($m['description'])): ?><div class="pm-desc"><?= e($m['description']) ?></div><?php endif; ?>
                    </div>
                </div>
                <div class="table-actions">
                    <button type="button" class="btn btn-sm btn-outline" data-pm-edit="<?= (int) $m['id'] ?>">Edit</button>
                    <form method="POST" action="/geprek-geh/admin/payments/<?= (int) $m['id'] ?>/toggle" class="inline-form">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm <?= $m['is_active'] ? 'btn-ghost' : 'btn-primary' ?>"><?= $m['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?></button>
                    </form>
                    <form method="POST" action="/geprek-geh/admin/payments/<?= (int) $m['id'] ?>/delete" class="inline-form" data-confirm="Hapus metode &ldquo;<?= e($m['name']) ?>&rdquo;? Pesanan lama tetap bisa dilihat, metode tidak lagi tersedia di checkout.">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-danger-ghost">Hapus</button>
                    </form>
                </div>
            </div>
            <form method="POST" action="/geprek-geh/admin/payments/<?= (int) $m['id'] ?>" class="pm-edit-form" data-pm-editform="<?= (int) $m['id'] ?>" hidden>
                <?= csrf_field() ?>
                <div class="form-row">
                    <div class="form-group">
                        <label>Tipe Pembayaran</label>
                        <div class="pm-type-row">
                            <label class="pm-type-opt">
                                <input type="radio" name="type" value="bank" <?= $m['type'] === 'bank' ? 'checked' : '' ?>>
                                <span>Transfer Bank</span>
                            </label>
                            <label class="pm-type-opt">
                                <input type="radio" name="type" value="ewallet" <?= $m['type'] === 'ewallet' ? 'checked' : '' ?>>
                                <span>E-Wallet</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="form-row pm-3col">
                    <div class="form-group"><label>Nama *</label><input type="text" name="name" class="input" value="<?= e($m['name']) ?>" required></div>
                    <div class="form-group"><label>Nomor *</label><input type="text" name="number" class="input" value="<?= e($m['number']) ?>" required></div>
                    <div class="form-group"><label>Atas Nama *</label><input type="text" name="holder" class="input" value="<?= e($m['holder']) ?>" required></div>
                </div>
                <div class="form-row pm-3col">
                    <div class="form-group">
                        <label>Keterangan</label>
                        <input type="text" name="description" class="input" value="<?= e($m['description'] ?? '') ?>" maxlength="255">
                    </div>
                    <div class="form-group">
                        <label>Urutan Tampil</label>
                        <input type="number" name="sort_order" class="input" min="0" value="<?= (int) $m['sort_order'] ?>">
                        <span class="field-hint">Angka kecil tampil duluan di checkout.</span>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <label class="pm-toggle">
                            <input type="checkbox" name="is_active" value="1" <?= $m['is_active'] ? 'checked' : '' ?>>
                            <span>Aktif</span>
                        </label>
                    </div>
                </div>
                <div class="settings-actions">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <button type="button" class="btn btn-ghost" data-pm-edit-close="<?= (int) $m['id'] ?>">Batal</button>
                </div>
            </form>
        </div>
        <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>
<?php endif; ?>

<script>
(function () {
    var toggle = function (el, show) { el.hidden = !show; };
    var addCard = document.querySelector('[data-pm-addcard]');
    var addBtn = document.querySelector('[data-pm-add]');
    var addClose = document.querySelector('[data-pm-add-close]');
    var lists = document.querySelectorAll('.pm-list');
    if (!lists.length) return;
    if (addBtn) addBtn.addEventListener('click', function () {
        toggle(addCard, true);
        addCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
    if (addClose) addClose.addEventListener('click', function () { toggle(addCard, false); });
    lists.forEach(function (list) {
        list.addEventListener('click', function (e) {
            var editBtn = e.target.closest('[data-pm-edit]');
            if (editBtn) {
                var id = editBtn.getAttribute('data-pm-edit');
                var form = list.querySelector('[data-pm-editform="' + id + '"]');
                toggle(form, form.hidden);
            }
            var closeBtn = e.target.closest('[data-pm-edit-close]');
            if (closeBtn) {
                var form2 = list.querySelector('[data-pm-editform="' + closeBtn.getAttribute('data-pm-edit-close') + '"]');
                toggle(form2, false);
            }
        });
    });
})();
</script>