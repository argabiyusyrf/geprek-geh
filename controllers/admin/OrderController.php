<?php
namespace Admin;
class OrderController {

    public static function pendingCount() {
        if (!\Auth::staff()) return 0;
        return (int) \Database::getInstance()->fetchColumn(
            "SELECT COUNT(*) FROM orders WHERE status = 'pending'"
        );
    }

    private function order($id) {
        return \Database::getInstance()->fetchOne(
            "SELECT o.*, u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone
             FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?",
            [$id]
        );
    }

    private function redirectBack($id) {
        gg_redirect("/admin/orders/{$id}");
        exit;
    }

    public function index() {
        \Auth::requireStaff();
        $db = \Database::getInstance();

        $allowed = ['pending','processing','shipped','delivered','cancelled'];
        $status = $_GET['status'] ?? '';
        if (!in_array($status, $allowed, true)) $status = '';

        $q = trim((string) ($_GET['q'] ?? ''));
        $sort = $_GET['sort'] ?? 'terbaru';
        $allowed_sort = ['terbaru','terlama','tertinggi','terendah'];
        if (!in_array($sort, $allowed_sort, true)) $sort = 'terbaru';

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $per = (int) ($_GET['per'] ?? 15);
        if (!in_array($per, [10, 15, 25, 50], true)) $per = 15;
        $offset = ($page - 1) * $per;

        $from = trim((string) ($_GET['from'] ?? ''));
        $to = trim((string) ($_GET['to'] ?? ''));
        $valid_date = fn($d) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && (bool) strtotime($d);
        if ($from !== '' && !$valid_date($from)) $from = '';
        if ($to !== '' && !$valid_date($to)) $to = '';
        if ($from !== '' && $to !== '' && strtotime($from) > strtotime($to)) {
            [$from, $to] = [$to, $from];
        }

        $date_where = '';
        $date_params = [];
        if ($from !== '') { $date_where .= ' AND o.created_at >= ?'; $date_params[] = $from . ' 00:00:00'; }
        if ($to !== '')   { $date_where .= ' AND o.created_at <= ?';  $date_params[] = $to . ' 23:59:59'; }

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
             FROM orders o WHERE 1=1{$date_where}",
            $date_params
        );
        $kpis = [
            'total'   => (int) $kpi['total'],
            'revenue' => (int) $kpi['revenue'],
            'unpaid'  => (int) $kpi['unpaid'],
            'active'  => (int) $kpi['active'],
        ];

        $status_counts = [];
        foreach ($db->fetchAll("SELECT status, COUNT(*) AS c FROM orders o WHERE 1=1{$date_where} GROUP BY status", $date_params) as $row) {
            $status_counts[$row['status']] = (int) $row['c'];
        }

        $where .= $date_where;
        $params = array_merge($params, $date_params);

        $total = (int) $db->fetchColumn(
            "SELECT COUNT(*) FROM orders o JOIN users u ON u.id = o.user_id WHERE {$where}",
            $params
        );
        $total_pages = max(1, (int) ceil($total / $per));
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
             WHERE {$where} ORDER BY {$sort_sql} LIMIT {$per} OFFSET {$offset}",
            $params
        );

        render('admin/orders/index', get_defined_vars());
    }

    public function show($id) {
        \Auth::requireStaff();
        $db = \Database::getInstance();
        $order = $this->order($id);
        if (!$order) {
            \flash_set('error', 'Pesanan tidak ditemukan.');
            gg_redirect('/admin/orders');
            exit;
        }
        $items = \order_items($id);
        $logs = $db->fetchAll(
            "SELECT * FROM order_logs WHERE order_id = ? ORDER BY created_at ASC, id ASC",
            [$id]
        );
        $app = require __DIR__ . '/../../config/app.php';
        $pm_details = \payment_method_details($order['payment_method']);
        $pm_type = \payment_method_type($order['payment_method']);
        $pm_label = \payment_method_label($order['payment_method']);
        $payment_details = ['bank' => $pm_type === 'bank' && $pm_details ? $pm_details : null, 'ewallet' => $pm_type === 'ewallet' && $pm_details ? $pm_details : null];

        [$payment_status_label, $payment_badge] = \format_payment_status($order['payment_status']);
        $transitions = $this->transitions($order['status']);
        $weekly_cancels = $this->weeklyCancelCount($db);

        render('admin/orders/show', get_defined_vars());
    }

    public function printOrder($id) {
        \Auth::requireStaff();
        $order = $this->order($id);
        if (!$order) {
            http_response_code(404);
            exit;
        }
        $items = \order_items($id);
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
        \Auth::requireStaff();
        if (!\verify_csrf()) {
            \flash_set('error', 'Token tidak valid.');
            $this->redirectBack($id);
        }
        $db = \Database::getInstance();
        $order = $this->order($id);
        if (!$order) {
            \flash_set('error', 'Pesanan tidak ditemukan.');
            gg_redirect('/admin/orders');
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

        if ($target === 'delivered' && \payment_method_type($order['payment_method']) === 'cod') {
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
            "/orders/{$id}"
        );

        // Email the customer when status actually changes (best-effort)
        \order_status_email($order['user_id'], $order['invoice_no'], \format_status($target)[0], $msg);

        \flash_set('success', 'Status pesanan diperbarui.');
        $this->redirectBack($id);
    }

    public function verifyPayment($id) {
        \Auth::requireStaff();
        if (!\verify_csrf()) {
            \flash_set('error', 'Token tidak valid.');
            $this->redirectBack($id);
        }
        $db = \Database::getInstance();
        $order = $this->order($id);
        if (!$order) {
            \flash_set('error', 'Pesanan tidak ditemukan.');
            gg_redirect('/admin/orders');
            exit;
        }

        if (!\payment_requires_proof($order['payment_method'])) {
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
            "/orders/{$id}"
        );

        \order_status_email($order['user_id'], $order['invoice_no'], 'Pembayaran LUNAS',
            'Pesanan kamu sedang diproses dapur.');

        \flash_set('success', 'Pembayaran diverifikasi. Pesanan lanjut diproses (LUNAS).');
        $this->redirectBack($id);
    }
}