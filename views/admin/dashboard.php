<?php $admin_page_title = 'Dashboard'; ?>

<div class="admin-hero">
    <div>
        <span class="admin-hero-eyebrow">Panel Admin · <?= e($admin_welcome_date) ?></span>
        <h2 class="admin-hero-title">Selamat bekerja, <?= e($_SESSION['user_name']) ?></h2>
        <p class="admin-hero-sub">
            <?php if ($stats['pending'] > 0): ?>
                Ada <strong><?= $stats['pending'] ?></strong> pesanan menunggu konfirmasi · hari ini <?= $stats['today_orders'] ?> pesanan masuk (<?= rupiah($stats['today_revenue']) ?>).
            <?php else: ?>
                Semua pesanan terkonfirmasi. Hari ini <?= $stats['today_orders'] ?> pesanan masuk (<?= rupiah($stats['today_revenue']) ?>).
            <?php endif; ?>
        </p>
    </div>
    <div class="admin-hero-actions">
        <a href="/geprek-geh/admin/products/create" class="btn btn-primary btn-sm">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
            Produk Baru
        </a>
        <a href="/geprek-geh/admin/orders" class="btn btn-ghost btn-sm">Kelola Pesanan</a>
    </div>
</div>

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

<div class="admin-grid-2 chart-grid">
    <div class="card chart-card">
        <div class="admin-card-head">
            <div class="admin-title min">
                <h3>Penjualan 7 Hari Terakhir</h3>
            </div>
            <span class="text-muted" style="font-size:12px;font-weight:700;">Pendapatan bersih</span>
        </div>
        <div class="chart-wrap">
            <?php
                $mx = max(1, max(array_column($sales7, 'revenue')));
                $W = 580; $H = 216; $padL = 48; $padR = 10; $padT = 16; $padB = 28;
                $pw = $W - $padL - $padR; $ph = $H - $padT - $padB;
                $fmtIdr = function ($n) {
                    $n = (float)$n;
                    if ($n >= 1000000) return trim(number_format($n / 1000000, 1, ',', '.'), '0,') . 'jt';
                    if ($n >= 1000)    return rtrim(rtrim(number_format($n / 1000, 1, ',', '.'), '0'), ',') . 'rb';
                    return (string)(int)$n;
                };
                $pts = [];
                foreach ($sales7 as $i => $s) {
                    $pts[] = ['x' => $padL + $i * ($pw / 6), 'y' => $padT + $ph - ($s['revenue'] / $mx) * $ph, 's' => $s];
                }
                $line = array_reduce($pts, fn($c, $p) => $c . round($p['x'], 1) . ',' . round($p['y'], 1) . ' ', '');
                $base = $padT + $ph;
                $area = 'M' . round($pts[0]['x'], 1) . ',' . round($pts[0]['y'], 1)
                      . implode('', array_map(fn($p) => ' L' . round($p['x'], 1) . ',' . round($p['y'], 1), array_slice($pts, 1)))
                      . ' L' . round($pts[6]['x'], 1) . ',' . $base . ' L' . round($pts[0]['x'], 1) . ',' . $base . ' Z';
            ?>
            <svg class="chart-svg" viewBox="0 0 <?= $W ?> <?= $H ?>" role="img" aria-label="Grafik penjualan 7 hari terakhir">
                <defs>
                    <linearGradient id="salesFill" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#D43E1B" stop-opacity="0.28"/>
                        <stop offset="100%" stop-color="#D43E1B" stop-opacity="0.02"/>
                    </linearGradient>
                </defs>
                <?php for ($k = 0; $k <= 4; $k++): $gy = round($padT + $ph * (1 - $k / 4)); $gv = $mx * $k / 4; ?>
                    <line class="chart-gridline" x1="<?= $padL ?>" y1="<?= $gy ?>" x2="<?= $W - $padR ?>" y2="<?= $gy ?>" stroke="rgba(20,17,12,0.07)" stroke-width="1" style="--d:<?= round(0.1 + $k * 0.06, 2) ?>s"/>
                    <text class="chart-tick" x="<?= $padL - 8 ?>" y="<?= $gy + 4 ?>" text-anchor="end" font-size="11" fill="#8A7A65" font-weight="600" style="--d:<?= round(0.25 + $k * 0.07, 2) ?>s"><?= $fmtIdr($gv) ?></text>
                <?php endfor; ?>
                <path class="chart-area" d="<?= $area ?>" fill="url(#salesFill)"/>
                <polyline class="chart-line" pathLength="1" points="<?= trim($line) ?>" fill="none" stroke="#D43E1B" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                <?php foreach ($pts as $i => $p): ?>
                    <circle class="chart-dot" cx="<?= round($p['x'], 1) ?>" cy="<?= round($p['y'], 1) ?>" r="4" fill="#FBF5E7" stroke="#D43E1B" stroke-width="2.5" style="--d:<?= round(0.45 + $i * 0.12, 2) ?>s">
                        <title><?= $p['s']['day'] ?> · <?= rupiah($p['s']['revenue']) ?> · <?= $p['s']['orders'] ?> pesanan</title>
                    </circle>
                <?php endforeach; ?>
                <?php foreach ($sales7 as $i => $s): $x = round($padL + $i * ($pw / 6), 1); ?>
                    <text class="chart-tick" x="<?= $x ?>" y="<?= $H - 6 ?>" text-anchor="middle" font-size="11" fill="#8A7A65" font-weight="600" style="--d:<?= round(0.25 + $i * 0.13, 2) ?>s"><?= $s['day'] ?></text>
                <?php endforeach; ?>
            </svg>
        </div>
    </div>

    <div class="card chart-card">
        <div class="admin-card-head">
            <div class="admin-title min">
                <h3>Status Pesanan</h3>
            </div>
            <span class="text-muted" style="font-size:12px;font-weight:700;"><?= $status_total ?> total</span>
        </div>
        <div class="chart-donut">
            <?php
                $statusColor = ['pending' => '#E8A21A', 'processing' => '#1F5FA8', 'shipped' => '#D43E1B', 'delivered' => '#2C6E3F', 'cancelled' => '#8A7A65'];
                $statusLabel = ['pending' => 'Menunggu', 'processing' => 'Diproses', 'shipped' => 'Dikirim', 'delivered' => 'Selesai', 'cancelled' => 'Dibatalkan'];
                $C = 2 * M_PI * 64; $off = 0;
            ?>
            <svg class="chart-svg donut-svg" viewBox="0 0 200 200" role="img" aria-label="Komposisi status pesanan">
                <g transform="rotate(-90 100 100)">
                    <circle cx="100" cy="100" r="64" fill="none" stroke="rgba(20,17,12,0.06)" stroke-width="26"/>
                    <?php if ($status_total > 0): foreach (['pending','processing','shipped','delivered','cancelled'] as $st):
                        $n = $status_dist[$st]; if ($n < 1) continue;
                        $seg = $n / $status_total * $C; ?>
                        <circle class="donut-seg" cx="100" cy="100" r="64" fill="none" stroke="<?= $statusColor[$st] ?>" stroke-width="26"
                                stroke-dasharray="<?= round($seg, 2) ?> <?= round($C - $seg, 2) ?>"
                                stroke-dashoffset="<?= round(-$off, 2) ?>"
                                style="--seg:<?= round($seg, 2) ?>; --off:<?= round(-$off, 2) ?>; --d:<?= round(0.15 + $off / $C * 0.9, 2) ?>s"/>
                    <?php $off += $seg; endforeach; endif; ?>
                </g>
                <text class="donut-count" x="100" y="97" text-anchor="middle" font-family="Fraunces,Georgia,serif" font-size="34" font-weight="700" fill="#14110C"><?= $status_total ?></text>
                <text class="donut-cap" x="100" y="119" text-anchor="middle" font-size="11" font-weight="700" letter-spacing="0.08em" fill="#8A7A65">PESANAN</text>
            </svg>
            <div class="chart-legend">
                <?php if ($status_total > 0): foreach (['pending','processing','shipped','delivered','cancelled'] as $st):
                    $n = $status_dist[$st]; if ($n < 1) continue; ?>
                    <div class="legend-item">
                        <span class="lg-dot" style="background:<?= $statusColor[$st] ?>"></span>
                        <span class="lg-name"><?= $statusLabel[$st] ?></span>
                        <span class="lg-sub"><?= round($n / $status_total * 100) ?>%</span>
                        <span class="lg-meta"><?= $n ?></span>
                    </div>
                <?php endforeach; else: ?>
                    <p class="text-muted">Belum ada pesanan.</p>
                <?php endif; ?>
            </div>
        </div>
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
                <span class="queue-total"><?= rupiah(grand_total($o)) ?></span>
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