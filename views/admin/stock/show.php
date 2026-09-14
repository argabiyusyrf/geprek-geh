<?php
$low = (int) $product['stock'] <= 0 ? 'out' : ((int) $product['stock'] <= $threshold ? 'low' : 'ok');
?>

<div class="breadcrumb">
    <a href="/geprek-geh/admin">Dashboard</a>
    <span>/</span>
    <a href="/geprek-geh/admin/stock">Manajemen Stok</a>
    <span>/</span>
    <span>Detail</span>
</div>

<div class="page-header page-header--wrap">
    <div>
        <h1><?= e($product['name']) ?></h1>
        <p class="page-sub"><?= e($product['category_name']) ?></p>
    </div>
</div>

<div class="stock-detail-hero">
    <div class="stock-detail-box">
        <span class="stat-label">Stok Saat Ini</span>
        <div class="stock-detail-qty">
            <span class="stock-pill <?= $low ?> stock-pill--lg"><?= (int) $product['stock'] ?></span>
            <?php if ($low === 'out'): ?>
                <span class="badge badge-danger">Stok Habis</span>
            <?php elseif ($low === 'low'): ?>
                <span class="badge badge-warning">Menipis (&#8804; <?= (int) $threshold ?>)</span>
            <?php else: ?>
                <span class="badge badge-success">Stok Aman</span>
            <?php endif; ?>
        </div>
        <?php if ((int) $product['stock'] > 0): ?>
            <p class="text-muted">Kurang lebih <?= (int) $product['stock'] ?> unit siap dijual.</p>
        <?php endif; ?>
        <div class="stock-detail-meta">
            <span>Harga: <?= rupiah((int) $product['price']) ?></span>
            <span>SKU: <?= e($product['slug']) ?></span>
            <span>Status: <?= $product['is_active'] ? 'Aktif' : 'Nonaktif' ?></span>
        </div>
    </div>

    <div class="card order-card stock-restock-card">
        <h3>Tambah Stok (Restock)</h3>
        <form method="POST" action="/geprek-geh/admin/stock/<?= (int) $product['id'] ?>/restock" class="stock-restock-form">
            <?= csrf_field() ?>
            <div class="form-row">
                <div class="form-group">
                    <label for="restock-qty">Jumlah Masuk</label>
                    <input type="number" id="restock-qty" name="qty" class="input" min="1" max="99999" required value="1">
                </div>
                <div class="form-group">
                    <label for="restock-note">Catatan (opsional)</label>
                    <input type="text" id="restock-note" name="note" class="input" placeholder="Contoh: order supplier #12" maxlength="255">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Penambahan Stok</button>
        </form>
    </div>
</div>

<div class="card order-card">
    <header class="order-card-head">
        <h3>Riwayat Perubahan Stok</h3>
    </header>
    <?php if (empty($movements)): ?>
        <div class="empty-state empty-state--compact">
            <span class="ghost"><svg style="width:4.5rem;height:4.5rem" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/><path d="M9 12h6"/><path d="M9 16h6"/></svg></span>
            <h3>Belum ada riwayat</h3>
            <p>Riwayat penambahan stok akan muncul di sini.</p>
        </div>
    <?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Perubahan</th>
                    <th>Stok Setelah</th>
                    <th>Catatan</th>
                    <th>Oleh</th>
                </tr>
            </thead>
            <tbody>
            <?php $running = null; ?>
            <?php foreach ($movements as $m): ?>
                <?php
                $running = ($running ?? (int) $product['stock']) - (int) $m['qty_change'];
                ?>
                <tr>
                    <td class="stock-cell"><?= e(date('d M Y H:i', strtotime($m['created_at']))) ?></td>
                    <td><span class="badge badge-success">+<?= (int) $m['qty_change'] ?></span></td>
                    <td class="stock-cell"><?= (int) $running ?></td>
                    <td><?= $m['note'] !== null ? e($m['note']) : '—' ?></td>
                    <td><?= $m['actor_name'] !== null ? e($m['actor_name']) : '—' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>