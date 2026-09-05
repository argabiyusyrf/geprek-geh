<?php
namespace Admin;
class DashboardController {
    public function index() {
        \Auth::requireAdmin();
        $db = \Database::getInstance();
        $stats = [
            'orders'    => $db->count('orders'),
            'revenue'   => (int)$db->fetchColumn("SELECT COALESCE(SUM(grand_total),0) FROM orders WHERE status != 'cancelled'"),
            'products'  => $db->count('products'),
            'customers' => $db->count('users', "role = 'customer'"),
            'pending'   => $db->count('orders', "status = 'pending'"),
        ];
        $recent_orders = $db->fetchAll(
            "SELECT o.*, u.name AS customer_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 10"
        );

        require __DIR__ . '/../../views/layouts/admin-header.php';
        require __DIR__ . '/../../views/admin/dashboard.php';
        require __DIR__ . '/../../views/layouts/admin-footer.php';
    }
}
