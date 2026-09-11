<?php $page_title = 'Pesanan Saya'; ?>

<div class="page-top">
    <header class="page-hero orders-hero">
        <p class="eyebrow">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2z"/><path d="M8 8h8M8 12h8M8 16h5"/></svg>
            Riwayat Pesanan
        </p>
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
        <span class="ghost">
            <svg width="96" height="96" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="0.9" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2z"/><path d="M8 8h8M8 12h8M8 16h5"/></svg>
        </span>
        <h3><?= $status !== '' ? 'Belum ada pesanan di kategori ini.' : 'Belum ada cerita pedas di sini.' ?></h3>
        <p><?= $status !== '' ? 'Coba pilih kategori lain, atau mulai pesan menu favoritmu sekarang.' : 'Saatnya menulis cerita pertama — pesan geprek andalanmu, kami antar panas.' ?></p>
        <a href="/geprek-geh/products" class="btn btn-primary">Mulai Pesan
            <span class="btn-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </span>
        </a>
    </div>
<?php else: ?>
    <div class="order-list">
        <?php foreach ($orders as $o):
            [$status_label, $badge_class] = format_status($o['status']);
            $can_reorder = in_array($o['status'], ['delivered', 'cancelled'], true);
            $items = $order_items[$o['id']] ?? [];
            $total_qty = array_sum(array_map(fn($i) => $i['quantity'], $items));
            $shown = array_slice($items, 0, 3);
            $more = count($items) - count($shown);
        ?>
            <div class="order-card-row">
                <a href="/geprek-geh/orders/<?= $o['id'] ?>" class="order-card" data-reveal>
                    <div class="order-card-header">
                        <span class="invoice"><?= e($o['invoice_no']) ?></span>
                        <span class="badge <?= $badge_class ?>"><?= $status_label ?></span>
                    </div>
                    <?php if ($items): ?>
                    <div class="order-card-items">
                        <?php foreach ($shown as $i): ?>
                            <?php if ($i['image']): ?>
                                <img class="order-thumb" src="/geprek-geh/assets/uploads/products/<?= e($i['image']) ?>" alt="<?= e($i['name']) ?>" loading="lazy">
                            <?php else: ?>
                                <span class="order-thumb order-thumb-art"><?= product_art($i['name'], $i['category_name'] ?? '', '', 56) ?></span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <?php if ($more > 0): ?><span class="order-thumb order-thumb-more">+<?= $more ?></span><?php endif; ?>
                        <span class="order-card-items-meta">
                            <?php $names = array_map(fn($i) => $i['name'], $items); ?>
                            <span class="order-items-name"><?= e($names[0]) ?></span>
                            <?php if (count($names) > 1): ?><span class="order-items-more">+<?= count($names) - 1 ?> lainnya</span><?php endif; ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    <div class="order-card-body">
                        <span class="order-date"><?= date('d M Y, H:i', strtotime($o['created_at'])) ?><?= $total_qty ? '<span class="order-porsi">' . $total_qty . ' porsi</span>' : '' ?></span>
                        <span class="order-total"><?= rupiah(grand_total($o)) ?></span>
                    </div>
                </a>
                <?php if ($can_reorder): ?>
                    <form method="POST" action="/geprek-geh/orders/<?= $o['id'] ?>/reorder" class="order-card-action">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-ghost btn-sm" title="Pesan ulang">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 2.64-6.36M3 3v6h6"/></svg>
                            Beli Lagi
                        </button>
                    </form>
                <?php endif; ?>
            </div>
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