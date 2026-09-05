<?php $admin_page_title = 'Dashboard'; ?>

<div class="stats-grid">
    <div class="stat-card stat-card--rev">
        <span class="stat-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </span>
        <div class="stat-number"><?= rupiah($stats['revenue']) ?></div>
        <div class="stat-label">Total Pendapatan</div>
    </div>
    <div class="stat-card stat-card--order">
        <span class="stat-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        </span>
        <div class="stat-number"><?= $stats['orders'] ?></div>
        <div class="stat-label">Total Pesanan</div>
    </div>
    <div class="stat-card stat-card--pending">
        <span class="stat-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
        </span>
        <div class="stat-number"><?= $stats['pending'] ?></div>
        <div class="stat-label">Menunggu Konfirmasi</div>
    </div>
    <div class="stat-card stat-card--prod">
        <span class="stat-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l8 4v6c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6z"/><path d="M9 12l2 2 4-4"/></svg>
        </span>
        <div class="stat-number"><?= $stats['products'] ?></div>
        <div class="stat-label">Total Produk</div>
    </div>
    <div class="stat-card stat-card--cust">
        <span class="stat-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </span>
        <div class="stat-number"><?= $stats['customers'] ?></div>
        <div class="stat-label">Total Pelanggan</div>
    </div>
</div>

<div class="admin-grid-2">
    <div class="card orders-queue">
        <div class="admin-card-head">
            <div class="admin-title min">
                <h3>Pesanan Terbaru</h3>
            </div>
            <a href="/geprek-geh/admin/orders" class="admin-link">Lihat semua
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7M9 7h8v8"/></svg>
            </a>
        </div>
        <?php if (empty($recent_orders)): ?>
            <p class="text-muted">Belum ada pesanan.</p>
        <?php else: ?>
        <div class="queue-list">
            <?php foreach ($recent_orders as $o):
                [$sl, $bc] = format_status($o['status']);
            ?>
            <a class="queue-item" href="/geprek-geh/admin/orders/<?= $o['id'] ?>">
                <span class="queue-icon">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </span>
                <span class="queue-body">
                    <strong class="queue-invoice">#<?= e($o['invoice_no']) ?></strong>
                    <span class="queue-meta"><?= e($o['customer_name']) ?> · <?= date('d M, H:i', strtotime($o['created_at'])) ?></span>
                </span>
                <span class="queue-total"><?= rupiah($o['grand_total']) ?></span>
                <span class="badge <?= $bc ?>"><?= $sl ?></span>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="card admin-quick">
        <div class="admin-card-head">
            <div class="admin-title min">
                <h3>Aksi Cepat</h3>
            </div>
        </div>
        <div class="quick-list">
            <a class="quick-item" href="/geprek-geh/admin/products/create">
                <span class="quick-icon quick-icon--accent">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                </span>
                <span><strong>Tambah Produk</strong><small>Masukkan menu baru ke katalog</small></span>
            </a>
            <a class="quick-item" href="/geprek-geh/admin/categories">
                <span class="quick-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7l9-4 9 4v10l-9 4-9-4z"/><path d="M3 7l9 4 9-4M12 11v10"/></svg>
                </span>
                <span><strong>Atur Kategori</strong><small>Kelompokkan menu agar mudah dicari</small></span>
            </a>
            <a class="quick-item" href="/geprek-geh/admin/users">
                <span class="quick-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/></svg>
                </span>
                <span><strong>Kelola Pelanggan</strong><small>Lihat &amp; kelola user terdaftar</small></span>
            </a>
        </div>
        <div class="admin-quick-foot">
            <span class="dot-pulse"></span>
            Pesanan baru masuk otomatis ter-queue di sini.
        </div>
    </div>
</div>