<?php
namespace Admin;
class DashboardController {
    public function index() {
        \Auth::requireStaff();
        $db = \Database::getInstance();
        $stats = [
            'orders'    => $db->count('orders'),
            'revenue'   => (int)$db->fetchColumn("SELECT COALESCE(SUM(total - discount + shipping_cost + tax),0) FROM orders WHERE status != 'cancelled'"),
            'products'  => $db->count('products'),
            'customers' => $db->count('users', "role = 'customer'"),
            'pending'   => $db->count('orders', "status = 'pending'"),
        ];
        $today = date('Y-m-d');
        $stats['today_orders']  = (int)$db->count('orders', "DATE(created_at) = '$today' AND status != 'cancelled'");
        $stats['today_revenue'] = (int)$db->fetchColumn("SELECT COALESCE(SUM(total - discount + shipping_cost + tax),0) FROM orders WHERE DATE(created_at) = '$today' AND status != 'cancelled'");

        $bulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        $hari  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
        $admin_welcome_date = $hari[(int)date('w')] . ', ' . (int)date('d') . ' ' . $bulan[(int)date('n') - 1] . ' ' . date('Y');

        // Penjualan 7 hari terakhir (area chart) — satu query GROUP BY untuk
        // seluruh rentang, bukan 7 query terpisah. Skalabel ke 30/90 hari.
        $grouped = [];
        foreach ($db->fetchAll(
            "SELECT DATE(created_at) AS day,
                    COALESCE(SUM(total - discount + shipping_cost + tax),0) AS rev,
                    COUNT(*) AS ordr
               FROM orders
              WHERE status != 'cancelled' AND created_at >= ?
              GROUP BY DATE(created_at)",
            [date('Y-m-d', strtotime('-6 days'))]
        ) as $r) {
            $grouped[$r['day']] = ['revenue' => (int) $r['rev'], 'orders' => (int) $r['ordr']];
        }
        $sales7 = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime("-$i days"));
            $g   = $grouped[$day] ?? ['revenue' => 0, 'orders' => 0];
            $sales7[] = [
                'day'     => date('d/m', strtotime($day)),
                'revenue' => $g['revenue'],
                'orders'  => $g['orders'],
            ];
        }

        // Donut: komposisi status pesanan
        $status_names = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        $status_dist  = array_fill_keys($status_names, 0);
        foreach ($db->fetchAll("SELECT status, COUNT(*) AS c FROM orders GROUP BY status") as $r) {
            if (isset($status_dist[$r['status']])) $status_dist[$r['status']] = (int)$r['c'];
        }
        $status_total = array_sum($status_dist);
        $recent_orders = $db->fetchAll(
            "SELECT o.*, u.name AS customer_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 10"
        );

        // Low-stock alert
        $app = require __DIR__ . '/../../config/app.php';
        $threshold = (int) ($app['stock_low_threshold'] ?? 10);
        $low_stock = \ProductRepo::lowStock($threshold, 8, false);
        $low_stock_count = (int) $db->count('products', 'is_active = 1 AND stock <= ' . (int) $threshold);
        $out_stock_count = (int) $db->count('products', 'is_active = 1 AND stock = 0');

        render('admin/dashboard', get_defined_vars());
    }
}
