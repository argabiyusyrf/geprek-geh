<?php
$admin_page_title = 'Kelola Kode Promo';
$status_tabs = [
    ''         => ['Semua', ''],
    'active'   => ['Aktif', 'good'],
    'upcoming' => ['Akan Tiba', 'info'],
    'expired'  => ['Berakhir', 'gray'],
    'inactive' => ['Nonaktif', 'bad'],
];
$sort_label = ['terbaru' => 'Terbaru', 'terlama' => 'Terlama', 'nama' => 'A–Z', 'nilai' => 'Diskon Terbesar'][$sort] ?? 'Terbaru';

$now = time();
$promo_state = function (array $p) use ($now): array {
    if ($p['expires_at'] && strtotime($p['expires_at']) < $now) return ['expired', 'Kedaluwarsa', 'badge-secondary'];
    if (!$p['is_active']) return ['inactive', 'Nonaktif', 'badge-danger'];
    if ($p['starts_at'] && strtotime($p['starts_at']) > $now) return ['upcoming', 'Akan Tiba', 'badge-info'];
    return ['active', 'Aktif', 'badge-success'];
};

$extra_q = [];
if ($q !== '') $extra_q['q'] = $q;
if ($sort !== 'terbaru') $extra_q['sort'] = $sort;
if ($per !== 15) $extra_q['per'] = $per;
$clear_params = $extra_q;
unset($clear_params['q']);
if ($status !== '') $clear_params['status'] = $status;
$search_clear_href = '/geprek-geh/admin/promos' . ($clear_params ? '?' . http_build_query($clear_params) : '');
$reset_href = '/geprek-geh/admin/promos';
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
if ($status !== '') $qp['status'] = $status;
$page_href = function (int $p) use ($qp) {
    return '/geprek-geh/admin/promos?' . http_build_query(['page' => $p] + $qp);
};
$days_left = fn($ts) => (int) ceil(($ts - $now) / 86400);
$days_past = fn($ts) => (int) floor(($now - $ts) / 86400);
?>

<div class="page-header page-header--wrap">
    <div>
        <h1>Kode Promo</h1>
        <p class="page-sub"><?= $kpis['total'] ?> kode promo &middot; <?= $kpis['aktif'] ?> aktif</p>
    </div>
    <button type="button" class="btn btn-primary" data-open-promo-drawer>+ Buat Kode Promo</button>
</div>

<div class="stats-grid stats-grid--promos">
    <div class="stat-card stat-card--rev">
        <span class="stat-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M20.6 13.4 11 3.8a2 2 0 0 0-1.4-.6H4a2 2 0 0 0-2 2v5.6c0 .5.2 1 .6 1.4l9.6 9.6a2 2 0 0 0 2.8 0l5.6-5.6a2 2 0 0 0 0-2.8Z"/><circle cx="7.5" cy="7.5" r="1.2"/></svg>
        </span>
        <div class="stat-number"><?= $kpis['total'] ?></div>
        <div class="stat-label">Total Kode</div>
    </div>
    <div class="stat-card stat-card--order">
        <span class="stat-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
        </span>
        <div class="stat-number"><?= $kpis['aktif'] ?></div>
        <div class="stat-label">Aktif</div>
    </div>
    <div class="stat-card stat-card--pending">
        <span class="stat-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
        </span>
        <div class="stat-number"><?= $kpis['akan_tiba'] ?></div>
        <div class="stat-label">Akan Tiba</div>
    </div>
    <div class="stat-card stat-card--prod">
        <span class="stat-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4m0 4h.01M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18Z"/></svg>
        </span>
        <div class="stat-number"><?= $kpis['berakhir'] ?></div>
        <div class="stat-label">Berakhir</div>
    </div>
    <div class="stat-card stat-card--cust">
        <span class="stat-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </span>
        <div class="stat-number"><?= $kpis['nonaktif'] ?></div>
        <div class="stat-label">Nonaktif</div>
    </div>
</div>

<div class="menu-filters admin-orders-filters">
    <form method="GET" action="/geprek-geh/admin/promos" class="menu-toolbar">
        <div class="menu-filter-row">
<?php if ($status !== ''): ?><input type="hidden" name="status" value="<?= e($status) ?>"><?php endif; ?>
            <?php if ($per !== 15): ?><input type="hidden" name="per" value="<?= e($per) ?>"><?php endif; ?>

            <div class="menu-toolbar-top">
                <div class="menu-search">
                    <svg class="menu-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                    <input type="search" name="q" placeholder="Cari kode promo…" value="<?= e($q) ?>" class="menu-search-input" autocomplete="off" aria-label="Cari kode promo">
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
                            <button type="button" class="menu-dropdown-item" data-value="nilai" role="option">Diskon Terbesar</button>
                        </div>
                        <select name="sort" class="menu-sort-select menu-sort-native" data-dropdown-select onchange="this.form.submit()" aria-label="Urutkan kode promo">
                            <option value="terbaru"<?= $sort === 'terbaru' ? ' selected' : '' ?>>Terbaru</option>
                            <option value="terlama"<?= $sort === 'terlama' ? ' selected' : '' ?>>Terlama</option>
                            <option value="nama"<?= $sort === 'nama' ? ' selected' : '' ?>>A–Z</option>
                            <option value="nilai"<?= $sort === 'nilai' ? ' selected' : '' ?>>Diskon Terbesar</option>
                        </select>
                        <svg class="menu-sort-chev menu-sort-chev--native" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                    </span>
                </span>
            </div>

            <div class="menu-cat-scroll orders-status-scroll">
                <div class="menu-category-pills">
                    <?php foreach ($status_tabs as $key => [$label, $tone]):
                        $pill_params = $extra_q;
                        if ($key !== '') $pill_params['status'] = $key;
                        $pill_href = '/geprek-geh/admin/promos' . ($pill_params ? '?' . http_build_query($pill_params) : '');
                        $count = $kpis[$key === 'active' ? 'aktif' : ($key === 'upcoming' ? 'akan_tiba' : ($key === 'expired' ? 'berakhir' : ($key === 'inactive' ? 'nonaktif' : 'total')))];
                    ?>
                        <a href="<?= e($pill_href) ?>"
                           class="menu-pill menu-pill--<?= $tone ?: 'all' ?> <?= $status === $key ? 'active' : '' ?>">
                            <?= e($label) ?>
                            <span class="menu-pill-count"><?= (int) $count ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="menu-results">
                <span class="menu-results-text">
                    <?php if ($q !== '' || $status !== ''): ?>
                        <?= $total ?> kode promo
                        <?php if ($q !== ''): ?> untuk "<strong><?= e($q) ?></strong>"<?php endif; ?>
                        <?php if ($status !== ''): ?> di <strong><?= e($status_tabs[$status][0]) ?></strong><?php endif; ?>
                        — <a href="<?= e($reset_href) ?>" class="menu-results-reset">Reset</a>
                    <?php else: ?>
                        Menampilkan <?= $total ?> kode promo
                    <?php endif; ?>
                    <span class="menu-results-hint">· <?= $kpis['berakhir'] ?> sudah kedaluwarsa</span>
                </span>
            </div>
        </div>
    </form>
</div>

<div class="table-wrap">
    <table class="table table--promos">
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nilai</th>
                <th>Min. Order</th>
                <th>Pemakaian</th>
                <th>Jadwal</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($promos)): ?>
                <tr>
                    <td colspan="7">
                        <div class="empty-state empty-state--compact">
                            <span class="ghost">🏷️</span>
                            <h3>Tidak ada kode promo</h3>
                            <p><?= $q !== '' || $status !== '' ? 'Coba ubah kata kunci atau filter status.' : 'Belum ada kode promo. Buatlah yang pertama.' ?></p>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
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
                [$state_key, $state_label, $state_badge] = $promo_state($p);
                $used = (int) $p['used_count'];
                $max = $p['max_uses'] ? (int) $p['max_uses'] : null;
                $pct = $max ? min(100, (int) round($used / $max * 100)) : 0;
                $quota_full = $max && $used >= $max;
                $exp_ts = $p['expires_at'] ? strtotime($p['expires_at']) : null;
                $start_ts = $p['starts_at'] ? strtotime($p['starts_at']) : null;
            ?>
            <tr class="promo-row" data-code="<?= e($p['code']) ?>">
                <td>
                    <div class="ocell-invoice">
                        <span class="ocell-no promo-code"><?= e($p['code']) ?></span>
                        <span class="ocell-sub">
                            <?= $start_ts && $start_ts > $now ? 'mulai ' . date('j M Y', $start_ts) : 'dibuat ' . date('j M Y', strtotime($p['created_at'])) ?>
                        </span>
                    </div>
                </td>
                <td class="stock-cell">
                    <?= $p['type'] === 'percentage' ? rtrim(rtrim((string) (float) $p['value'], '0'), '.') . '%' : rupiah((float) $p['value']) ?>
                    <span class="table-slug"><?= $p['type'] === 'percentage' ? 'persentase' : 'potongan tetap' ?></span>
                </td>
                <td class="stock-cell"><?= $p['min_order'] ? rupiah($p['min_order']) : '—' ?></td>
                <td>
                    <?php if ($max): ?>
                        <div class="promo-quota">
                            <div class="promo-quota-track"><span class="promo-quota-fill <?= $quota_full ? 'full' : '' ?>" style="width: <?= $pct ?>%"></span></div>
                            <span class="promo-quota-text">
                                <?= $used ?>/<?= $max ?>
                                <?php if ($quota_full): ?><span class="promo-quota-low">habis</span>
                                <?php elseif ($max - $used <= 5): ?><span class="promo-quota-low">sisa <?= $max - $used ?></span>
                                <?php else: ?>· sisa <?= $max - $used ?><?php endif; ?>
                            </span>
                        </div>
                    <?php else: ?>
                        <span class="stock-cell"><?= $used ?></span>
                        <span class="table-slug">tanpa batas</span>
                    <?php endif; ?>
                </td>
                <td class="stock-cell">
                    <?php if ($exp_ts): ?>
                        <?= date('j M Y', $exp_ts) ?>
                        <span class="promo-sub">
                            <?php if ($exp_ts < $now): ?><?= $days_past($exp_ts) > 0 ? $days_past($exp_ts) . ' hari lalu' : 'hari ini' ?>
                            <?php else: ?>sisa <?= $days_left($exp_ts) ?> hari<?php endif; ?>
                        </span>
                    <?php else: ?>
                        —
                        <span class="promo-sub">tanpa batas waktu</span>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="badge <?= $state_badge ?>"><?= $state_label ?></span>
                    <?php if ($state_key === 'active' && $quota_full): ?>
                        <span class="promo-quota-badge">kuota habis</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="table-actions">
                        <button type="button" class="btn btn-sm btn-outline" data-edit-promo='<?= $pJson ?>'>Edit</button>
                        <form method="POST" action="/geprek-geh/admin/promos/<?= $p['id'] ?>/toggle" class="inline-form">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-ghost"><?= $state_key === 'active' ? 'Nonaktifkan' : 'Aktifkan' ?></button>
                        </form>
                        <form method="POST" action="/geprek-geh/admin/promos/<?= $p['id'] ?>/delete" class="inline-form inline-form--compact" data-confirm="Hapus kode promo <?= e($p['code']) ?>?">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($total > 0): ?>
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
    <form method="GET" action="/geprek-geh/admin/promos" class="menu-perpage">
        <?php if ($status !== ''): ?><input type="hidden" name="status" value="<?= e($status) ?>"><?php endif; ?>
        <?php if ($q !== ''): ?><input type="hidden" name="q" value="<?= e($q) ?>"><?php endif; ?>
        <?php if ($sort !== 'terbaru'): ?><input type="hidden" name="sort" value="<?= e($sort) ?>"><?php endif; ?>
        <label for="promos-per-page" class="menu-perpage-label">Baris per halaman</label>
        <select id="promos-per-page" name="per" class="menu-perpage-select" onchange="this.form.submit()" aria-label="Baris per halaman">
            <?php foreach ([10, 15, 25, 50] as $pp): ?>
                <option value="<?= $pp ?>"<?= $per === $pp ? ' selected' : '' ?>><?= $pp ?> baris</option>
            <?php endforeach; ?>
        </select>
    </form>
</nav>
<?php endif; ?>

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

    <form method="POST" action="/geprek-geh/admin/promos" class="drawer-body" id="promo-form" novalidate>
        <?= csrf_field() ?>

        <div class="form-group">
            <label>Kode Promo <span class="req">*</span></label>
            <input type="text" name="code" class="input promo-code-input <?= !empty($formErrors['code']) ? 'is-invalid' : '' ?>" value="<?= e(\fval($formOld, [], 'code')) ?>" placeholder="WELCOME10" required maxlength="32" style="text-transform:uppercase" pattern="[A-Za-z0-9]+" title="Hanya huruf dan angka, tanpa spasi.">
            <?php if (!empty($formErrors['code'])): ?><span class="field-error"><?= e($formErrors['code']) ?></span><?php endif; ?>
            <span class="field-hint">Huruf besar/angka tanpa spasi dan simbol.</span>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Tipe Diskon</label>
                <select name="type" class="input promo-type-select">
                    <option value="percentage" <?= \fval($formOld, [], 'type') === 'percentage' ? 'selected' : '' ?>>Persentase (%)</option>
                    <option value="fixed" <?= \fval($formOld, [], 'type') === 'fixed' ? 'selected' : '' ?>>Nominal Tetap (Rp)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Nilai <span class="req">*</span></label>
                <input type="number" name="value" id="promo-value" class="input <?= !empty($formErrors['value']) ? 'is-invalid' : '' ?>" step="0.01" min="0" required>
                <?php if (!empty($formErrors['value'])): ?><span class="field-error"><?= e($formErrors['value']) ?></span><?php endif; ?>
                <span class="field-hint promo-value-hint"></span>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Min. Order (Rp)</label>
                <input type="number" name="min_order" class="input" min="0" placeholder="50000">
                <span class="field-hint">Batas bawah belanja untuk memakai kode ini.</span>
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
                <input type="datetime-local" name="starts_at" class="input" id="promo-starts">
            </div>
            <div class="form-group">
                <label>Kedaluwarsa</label>
                <input type="datetime-local" name="expires_at" class="input <?= !empty($formErrors['expires_at']) ? 'is-invalid' : '' ?>" id="promo-expires">
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