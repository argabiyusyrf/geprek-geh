<?php
namespace Admin;
class DashboardController {
    public function index() {
        \Auth::requireAdmin();
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
        $recent_orders = $db->fetchAll(
            "SELECT o.*, u.name AS customer_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 10"
        );

        require __DIR__ . '/../../views/layouts/admin-header.php';
        require __DIR__ . '/../../views/admin/dashboard.php';
        require __DIR__ . '/../../views/layouts/admin-footer.php';
    }
}
