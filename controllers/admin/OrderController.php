<?php
namespace Admin;
class OrderController {

    private function order($id) {
        return \Database::getInstance()->fetchOne(
            "SELECT o.*, u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone
             FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?",
            [$id]
        );
    }

    private function redirectBack($id) {
        header("Location: /geprek-geh/admin/orders/{$id}");
        exit;
    }

    public function index() {
        \Auth::requireAdmin();
        $db = \Database::getInstance();

        $allowed = ['pending','processing','shipped','delivered','cancelled'];
        $status = $_GET['status'] ?? '';
        if (!in_array($status, $allowed, true)) $status = '';

        $q = trim((string) ($_GET['q'] ?? ''));
        $sort = $_GET['sort'] ?? 'terbaru';
        $allowed_sort = ['terbaru','terlama','tertinggi','terendah'];
        if (!in_array($sort, $allowed_sort, true)) $sort = 'terbaru';

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $per_page = 15;
        $offset = ($page - 1) * $per_page;

        $where = '1=1';
        $params = [];
        if ($status !== '') {
            $where .= " AND o.status = ?";
            $params[] = $status;
        }
        if ($q !== '') {
            $like = '%' . $q . '%';
            $where .= " AND (o.invoice_no LIKE ? OR u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ? OR EXISTS (
                SELECT 1 FROM order_items oi JOIN products p ON p.id = oi.product_id
                WHERE oi.order_id = o.id AND p.name LIKE ?
            ))";
            array_push($params, $like, $like, $like, $like, $like);
        }

        $kpi = $db->fetchOne(
            "SELECT
                COUNT(*) AS total,
                COALESCE(SUM(CASE WHEN o.payment_status = 'paid' THEN (o.total - o.discount + o.shipping_cost + o.tax) ELSE 0 END), 0) AS revenue,
                COALESCE(SUM(CASE WHEN o.payment_status = 'unpaid' AND o.status NOT IN ('cancelled','delivered') THEN 1 ELSE 0 END), 0) AS unpaid,
                COALESCE(SUM(CASE WHEN o.status IN ('processing','shipped') THEN 1 ELSE 0 END), 0) AS active
             FROM orders o"
        );
        $kpis = [
            'total'   => (int) $kpi['total'],
            'revenue' => (int) $kpi['revenue'],
            'unpaid'  => (int) $kpi['unpaid'],
            'active'  => (int) $kpi['active'],
        ];

        $status_counts = [];
        foreach ($db->fetchAll("SELECT status, COUNT(*) AS c FROM orders GROUP BY status") as $row) {
            $status_counts[$row['status']] = (int) $row['c'];
        }

        $total = (int) $db->fetchColumn(
            "SELECT COUNT(*) FROM orders o JOIN users u ON u.id = o.user_id WHERE {$where}",
            $params
        );
        $total_pages = max(1, ceil($total / $per_page));
        if ($page > $total_pages) $page = $total_pages;

        $order_dir = [
            'terbaru'   => 'o.created_at DESC',
            'terlama'   => 'o.created_at ASC',
            'tertinggi' => '(o.total - o.discount + o.shipping_cost + o.tax) DESC',
            'terendah'  => '(o.total - o.discount + o.shipping_cost + o.tax) ASC',
        ];
        $sort_sql = $order_dir[$sort];

        $orders = $db->fetchAll(
            "SELECT o.*, u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone,
                (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) AS item_count
             FROM orders o JOIN users u ON u.id = o.user_id
             WHERE {$where} ORDER BY {$sort_sql} LIMIT {$per_page} OFFSET {$offset}",
            $params
        );

        require __DIR__ . '/../../views/layouts/admin-header.php';
        require __DIR__ . '/../../views/admin/orders/index.php';
        require __DIR__ . '/../../views/layouts/admin-footer.php';
    }

    public function show($id) {
        \Auth::requireAdmin();
        $db = \Database::getInstance();
        $order = $this->order($id);
        if (!$order) {
            \flash_set('error', 'Pesanan tidak ditemukan.');
            header('Location: /geprek-geh/admin/orders');
            exit;
        }
        $items = $db->fetchAll(
            "SELECT oi.*, p.name, p.image, p.slug
             FROM order_items oi JOIN products p ON oi.product_id = p.id
             WHERE oi.order_id = ?",
            [$id]
        );
        $logs = $db->fetchAll(
            "SELECT * FROM order_logs WHERE order_id = ? ORDER BY created_at ASC, id ASC",
            [$id]
        );
        $app = require __DIR__ . '/../../config/app.php';
        $payment_details = $app['payment'] ?? [];

        [$payment_status_label, $payment_badge] = \format_payment_status($order['payment_status']);
        $transitions = $this->transitions($order['status']);
        $weekly_cancels = $this->weeklyCancelCount($db);

        require __DIR__ . '/../../views/layouts/admin-header.php';
        require __DIR__ . '/../../views/admin/orders/show.php';
        require __DIR__ . '/../../views/layouts/admin-footer.php';
    }

    public function printOrder($id) {
        \Auth::requireAdmin();
        $db = \Database::getInstance();
        $order = $this->order($id);
        if (!$order) {
            http_response_code(404);
            exit;
        }
        $items = $db->fetchAll(
            "SELECT oi.*, p.name, p.image, p.slug
             FROM order_items oi JOIN products p ON oi.product_id = p.id
             WHERE oi.order_id = ?",
            [$id]
        );
        $app = require __DIR__ . '/../../config/app.php';
        $payment_details = $app['payment'] ?? [];
        require __DIR__ . '/../../views/admin/orders/print.php';
    }

    const CANCEL_LIMIT = 3;
    const CANCEL_WINDOW_DAYS = 7;

    private function weeklyCancelCount($db) {
        return (int) $db->fetchColumn(
            "SELECT COUNT(*) FROM order_logs
             WHERE actor = 'admin' AND message LIKE '%dibatalkan oleh admin%'
             AND created_at >= DATE_SUB(NOW(), INTERVAL " . self::CANCEL_WINDOW_DAYS . " DAY)"
        );
    }

    private function transitions($status) {
        $map = [
            'pending'    => ['processing', 'cancelled'],
            'processing' => ['shipped'],
            'shipped'    => ['delivered'],
            'delivered'  => [],
            'cancelled'  => [],
        ];
        return $map[$status] ?? [];
    }

    public function updateStatus($id) {
        \Auth::requireAdmin();
        if (!\verify_csrf()) {
            \flash_set('error', 'Token tidak valid.');
            $this->redirectBack($id);
        }
        $db = \Database::getInstance();
        $order = $this->order($id);
        if (!$order) {
            \flash_set('error', 'Pesanan tidak ditemukan.');
            header('Location: /geprek-geh/admin/orders');
            exit;
        }

        $target = $_POST['status'] ?? '';
        $transitions = $this->transitions($order['status']);
        if (!in_array($target, $transitions, true)) {
            \flash_set('error', "Transisi tidak valid dari status \"{$order['status']}\".");
            $this->redirectBack($id);
        }

        $data = ['status' => $target];

        if ($target === 'shipped') {
            $tracking = trim($_POST['tracking_no'] ?? '');
            if ($tracking !== '') $data['tracking_no'] = $tracking;
        }

        if ($target === 'cancelled') {
            $reason = trim($_POST['cancel_reason'] ?? '');
            if ($reason === '') {
                \flash_set('error', 'Alasan pembatalan wajib diisi.');
                $this->redirectBack($id);
            }
            if ($this->weeklyCancelCount($db) >= self::CANCEL_LIMIT) {
                \flash_set('error', 'Batas pembatalan mingguan tercapai (maks. ' . self::CANCEL_LIMIT . ' dalam ' . self::CANCEL_WINDOW_DAYS . ' hari).');
                $this->redirectBack($id);
            }
            $data['cancel_reason'] = mb_substr($reason, 0, 255);
            if ($order['payment_status'] === 'paid') $data['payment_status'] = 'refunded';
        }

        if ($target === 'delivered' && $order['payment_method'] === 'cod') {
            $data['payment_status'] = 'paid';
        }

        $db->update('orders', $data, 'id = ?', [$id]);

        if ($target === 'cancelled') {
            \order_restore_stock($db, $id);
            \order_log($db, $id, 'admin', 'Pesanan dibatalkan oleh admin — stok dikembalikan'
                . ($order['payment_status'] === 'paid' ? ', pembayaran di-refund' : ''));
        } else {
            \order_log($db, $id, 'admin', 'Status diubah menjadi ' . \format_status($target)[0]
                . ($target === 'shipped' && !empty($data['tracking_no']) ? ' — resi ' . $data['tracking_no'] : ''));
        }

        $msg = 'Status: ' . \format_status($target)[0] . '.';
        if ($target === 'cancelled') {
            $msg = "Pesanan dibatalkan. Alasan: {$reason}."
                . ($order['payment_status'] === 'paid' ? ' Pembayaran yang sudah lunas akan di-refund.' : '');
        } elseif ($target === 'shipped' && !empty($data['tracking_no'])) {
            $msg = 'Pesanan sedang dikirim. Nomor resi: ' . $data['tracking_no'] . '.';
        }
        \NotificationController::push(
            $order['user_id'],
            'order',
            "Pesanan {$order['invoice_no']} diperbarui",
            $msg,
            "/geprek-geh/orders/{$id}"
        );

        // Email the customer when status actually changes (best-effort)
        try {
            $customer = $db->fetchOne("SELECT email, name, notify_email FROM users WHERE id = ?", [$order['user_id']]);
            if ($customer && !empty($customer['email']) && (int)($customer['notify_email'] ?? 1) === 1) {
                \Mail::orderStatusChanged($customer['email'], $customer['name'], $order['invoice_no'], \format_status($target)[0], $msg);
            }
        } catch (\Exception $e) {
            error_log('[AdminOrder] status email failed: ' . $e->getMessage());
        }

        \flash_set('success', 'Status pesanan diperbarui.');
        $this->redirectBack($id);
    }

    public function verifyPayment($id) {
        \Auth::requireAdmin();
        if (!\verify_csrf()) {
            \flash_set('error', 'Token tidak valid.');
            $this->redirectBack($id);
        }
        $db = \Database::getInstance();
        $order = $this->order($id);
        if (!$order) {
            \flash_set('error', 'Pesanan tidak ditemukan.');
            header('Location: /geprek-geh/admin/orders');
            exit;
        }

        if (!in_array($order['payment_method'], ['transfer', 'ewallet'], true)) {
            \flash_set('error', 'Pesanan COD lunas otomatis saat pesanan diterima.');
            $this->redirectBack($id);
        }
        if ($order['payment_status'] === 'paid') {
            \flash_set('error', 'Pembayaran pesanan ini sudah lunas.');
            $this->redirectBack($id);
        }
        if (!$order['payment_proof']) {
            \flash_set('error', 'Belum ada bukti pembayaran untuk diverifikasi.');
            $this->redirectBack($id);
        }
        if (in_array($order['status'], ['cancelled', 'delivered'], true)) {
            \flash_set('error', 'Pembayaran tidak bisa diverifikasi pada status ini.');
            $this->redirectBack($id);
        }

        $db->update('orders', ['payment_status' => 'paid'], 'id = ?', [$id]);
        \order_log($db, $id, 'admin', 'Pembayaran terverifikasi — LUNAS');

        if ($order['status'] === 'pending') {
            $db->update('orders', ['status' => 'processing'], 'id = ?', [$id]);
            \order_log($db, $id, 'admin', 'Status diubah menjadi ' . \format_status('processing')[0]);
        }

        \NotificationController::push(
            $order['user_id'],
            'payment',
            "Pembayaran {$order['invoice_no']} terverifikasi",
            'Pembayaran LUNAS. Pesanan kamu sedang diproses dapur.',
            "/geprek-geh/orders/{$id}"
        );

        try {
            $customer = $db->fetchOne("SELECT email, name, notify_email FROM users WHERE id = ?", [$order['user_id']]);
            if ($customer && !empty($customer['email']) && (int)($customer['notify_email'] ?? 1) === 1) {
                \Mail::orderStatusChanged($customer['email'], $customer['name'], $order['invoice_no'], 'Pembayaran LUNAS', 'Pesanan kamu sedang diproses dapur.');
            }
        } catch (\Exception $e) {
            error_log('[AdminOrder] payment email failed: ' . $e->getMessage());
        }

        \flash_set('success', 'Pembayaran diverifikasi. Pesanan lanjut diproses (LUNAS).');
        $this->redirectBack($id);
    }
}