<div class="page-head" data-reveal>
    <span class="overline">Inbox</span>
    <h1>Notifikasi</h1>
    <p class="page-head-lead">Semua notifikasi pesanan dan aktivitas akunmu.</p>
</div>

<section class="card notif-page-card" data-reveal>
    <?php if (empty($notifications)): ?>
        <div class="empty-state empty-state--compact">
            <span class="ghost">
                <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            </span>
            <h3>Belum ada notifikasi</h3>
            <p>Notifikasi pesanan akan muncul di sini.</p>
        </div>
    <?php else: ?>
        <?php
        $prev_date = null;
        foreach ($notifications as $n):
            $d = date('Y-m-d', strtotime($n['created_at']));
            if ($d !== $prev_date):
                $prev_date = $d;
        ?>
            <div class="notif-page-date"><?= date('d M Y', strtotime($d)) ?></div>
        <?php endif; ?>
        <a href="<?= e($n['link'] ?? '/geprek-geh/account') ?>" class="notif-page-item<?= $n['is_read'] ? '' : ' unread' ?>"
           data-page-read-url="<?= $n['is_read'] ? '' : '/geprek-geh/account/notifications/' . (int)$n['id'] . '/read' ?>">
            <span class="notif-pip"></span>
            <span class="notif-body">
                <span class="notif-title"><?= e($n['title']) ?></span>
                <?php if ($n['message']): ?><span class="notif-msg"><?= e($n['message']) ?></span><?php endif; ?>
                <span class="notif-time"><?= time_ago($n['created_at']) ?></span>
            </span>
        </a>
        <?php endforeach; ?>
    <?php endif; ?>
</section>

<?php if ($pages > 1): ?>
<nav class="pagination" data-reveal aria-label="Navigasi halaman">
    <?php if ($page > 1): ?>
        <a class="pagination-link" href="/geprek-geh/account/notifications?page=<?= $page - 1 ?>">Sebelumnya</a>
    <?php else: ?>
        <span class="pagination-link is-disabled">Sebelumnya</span>
    <?php endif; ?>
    <span class="pagination-info">Halaman <?= $page ?> dari <?= $pages ?></span>
    <?php if ($page < $pages): ?>
        <a class="pagination-link" href="/geprek-geh/account/notifications?page=<?= $page + 1 ?>">Berikutnya</a>
    <?php else: ?>
        <span class="pagination-link is-disabled">Berikutnya</span>
    <?php endif; ?>
</nav>
<?php endif; ?>