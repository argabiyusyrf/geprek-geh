<?php $page_title = 'Pesanan Saya'; ?>

<div class="page-top">
    <header class="page-hero">
        <h1>Pesanan Saya</h1>
        <p class="sub">Pantau status pesananmu — mulai dari diproses, diantar, sampai siap disantap.</p>
    </header>
</div>

<?php
    $filter_tabs = [
        '' => 'Semua',
        'pending' => 'Menunggu',
        'processing' => 'Diproses',
        'shipped' => 'Dikirim',
        'delivered' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];
?>

<div class="order-filter" data-reveal>
    <?php foreach ($filter_tabs as $key => $label): ?>
        <?php
            $count = $key === '' ? $all_count : ($status_counts[$key] ?? 0);
            $active = ($status ?? '') === $key;
        ?>
        <a href="?status=<?= e($key) ?>" class="order-filter-tab <?= $active ? 'is-active' : '' ?>">
            <?= e($label) ?>
            <span class="order-filter-count"><?= $count ?></span>
        </a>
    <?php endforeach; ?>
</div>

<?php if (empty($orders)): ?>
    <div class="empty-state">
        <span class="ghost"><svg width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2z"/><path d="M8 8h8M8 12h8M8 16h5"/></svg></span>
        <p><?= $status !== '' ? 'Belum ada pesanan dengan status ini.' : 'Belum ada pesanan. Yuk mulai belanja menu favoritmu.' ?></p>
        <a href="/geprek-geh/products" class="btn btn-primary">Mulai Belanja
            <span class="btn-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </span>
        </a>
    </div>
<?php else: ?>
    <div class="order-list">
        <?php foreach ($orders as $o):
            [$status_label, $badge_class] = format_status($o['status']);
        ?>
            <a href="/geprek-geh/orders/<?= $o['id'] ?>" class="order-card" data-reveal>
                <div class="order-card-header">
                    <span class="invoice"><?= e($o['invoice_no']) ?></span>
                    <span class="badge <?= $badge_class ?>"><?= $status_label ?></span>
                </div>
                <div class="order-card-body">
                    <span class="order-date"><?= date('d M Y, H:i', strtotime($o['created_at'])) ?></span>
                    <span class="order-total"><?= rupiah($o['grand_total']) ?></span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if ($total_pages > 1): ?>
    <nav class="order-pagination" aria-label="Navigasi halaman">
        <?php
        $qp = [];
        if ($status !== '') $qp['status'] = $status;
        ?>
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>&<?= http_build_query($qp) ?>" class="menu-page-btn">&laquo;</a>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?= $i ?>&<?= http_build_query($qp) ?>"
               class="menu-page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page < $total_pages): ?>
            <a href="?page=<?= $page + 1 ?>&<?= http_build_query($qp) ?>" class="menu-page-btn">&raquo;</a>
        <?php endif; ?>
    </nav>
    <?php endif; ?>
<?php endif; ?>