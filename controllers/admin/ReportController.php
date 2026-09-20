<?php
namespace Admin;
class ReportController {

    public function index() {
        \Auth::requireStaff();

        [$from, $to] = $this->parseRange();
        extract($this->collect($from, $to));

        $admin_page_title = 'Laporan Penjualan';
        render('admin/reports/index', get_defined_vars());
    }

    /** Halaman cetak laporan — layout mandiri (print-friendly). */
    public function printPage() {
        \Auth::requireStaff();

        [$from, $to] = $this->parseRange();
        extract($this->collect($from, $to));

        require __DIR__ . '/../../views/admin/reports/print.php';
        exit;
    }

    private function parseRange(): array {
        $from = trim((string) ($_GET['from'] ?? ''));
        $to   = trim((string) ($_GET['to'] ?? ''));
        $valid_date = fn($d) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && (bool) strtotime($d);
        if ($from !== '' && !$valid_date($from)) $from = '';
        if ($to   !== '' && !$valid_date($to))   $to = '';
        if ($from === '' && $to === '') {
            $from = date('Y-m-d', strtotime('-30 days'));
            $to   = date('Y-m-d');
        }
        if ($from !== '' && $to !== '' && strtotime($from) > strtotime($to)) {
            [$from, $to] = [$to, $from];
        }
        return [$from, $to];
    }

    private function collect(string $from, string $to): array {
        $db = \Database::getInstance();

        $date_where = '';
        $params = [];
        if ($from !== '') { $date_where .= ' AND o.created_at >= ?'; $params[] = $from . ' 00:00:00'; }
        if ($to   !== '') { $date_where .= ' AND o.created_at <= ?'; $params[] = $to   . ' 23:59:59'; }

        $kpis = $db->fetchOne(
            "SELECT
                COUNT(*) AS total_orders,
                COALESCE(SUM(o.total - o.discount + o.shipping_cost + o.tax), 0) AS revenue,
                COALESCE(SUM(oi.quantity), 0) AS total_qty,
                COALESCE(AVG(o.total - o.discount + o.shipping_cost + o.tax), 0) AS avg_order
             FROM orders o
             LEFT JOIN order_items oi ON oi.order_id = o.id
             WHERE o.status != 'cancelled'{$date_where}",
            $params
        );

        $daily = $db->fetchAll(
            "SELECT DATE(o.created_at) AS tgl,
                COUNT(*) AS order_count,
                COALESCE(SUM(o.total - o.discount + o.shipping_cost + o.tax), 0) AS revenue,
                COALESCE(SUM(oi.quantity), 0) AS qty
             FROM orders o
             LEFT JOIN order_items oi ON oi.order_id = o.id
             WHERE o.status != 'cancelled'{$date_where}
             GROUP BY DATE(o.created_at) ORDER BY tgl DESC",
            $params
        );

        // Graf: isi tanggal kosong agar sumbu kontinu.
        $by_day = [];
        foreach ($daily as $r) { $by_day[$r['tgl']] = $r; }
        $chart = [];
        if ($from !== '' && $to !== '') {
            for ($d = strtotime($from); $d <= strtotime($to); $d = strtotime('+1 day', $d)) {
                $tgl = date('Y-m-d', $d);
                $chart[] = $by_day[$tgl] ?? ['tgl' => $tgl, 'order_count' => 0, 'revenue' => 0, 'qty' => 0];
            }
            $chart = array_slice($chart, -31, 31); // maks 31 bar untuk keterbacaan grafik
        } else {
            $chart = array_reverse($daily);
        }

        $top_products = $db->fetchAll(
            "SELECT p.name, SUM(oi.quantity) AS qty, SUM(oi.quantity * oi.price) AS revenue
             FROM order_items oi
             JOIN products p ON p.id = oi.product_id
             JOIN orders o ON o.id = oi.order_id
             WHERE o.status != 'cancelled'{$date_where}
             GROUP BY p.id ORDER BY revenue DESC LIMIT 10",
            $params
        );

        $by_category = $db->fetchAll(
            "SELECT c.name, SUM(oi.quantity) AS qty, SUM(oi.quantity * oi.price) AS revenue
             FROM order_items oi
             JOIN products p ON p.id = oi.product_id
             JOIN categories c ON c.id = p.category_id
             JOIN orders o ON o.id = oi.order_id
             WHERE o.status != 'cancelled'{$date_where}
             GROUP BY c.id ORDER BY revenue DESC",
            $params
        );

        $by_method = $db->fetchAll(
            "SELECT o.payment_method, COUNT(*) AS cnt,
                COALESCE(SUM(o.total - o.discount + o.shipping_cost + o.tax), 0) AS revenue
             FROM orders o WHERE o.status != 'cancelled'{$date_where}
             GROUP BY o.payment_method ORDER BY revenue DESC",
            $params
        );

        return compact('kpis', 'daily', 'chart', 'top_products', 'by_category', 'by_method');
    }

    public function export() {
        \Auth::requireStaff();
        $db = \Database::getInstance();

        $from = trim((string) ($_GET['from'] ?? date('Y-m-d', strtotime('-30 days'))));
        $to   = trim((string) ($_GET['to'] ?? date('Y-m-d')));
        $valid_date = fn($d) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && (bool) strtotime($d);
        if (!$valid_date($from)) $from = date('Y-m-d', strtotime('-30 days'));
        if (!$valid_date($to))   $to   = date('Y-m-d');

        $date_where = ' AND o.created_at >= ? AND o.created_at <= ?';
        $params = [$from . ' 00:00:00', $to . ' 23:59:59'];

        $daily = $db->fetchAll(
            "SELECT DATE(o.created_at) AS tgl,
                COUNT(*) AS order_count,
                COALESCE(SUM(o.total - o.discount + o.shipping_cost + o.tax), 0) AS revenue,
                COALESCE(SUM(oi.quantity), 0) AS qty
             FROM orders o
             LEFT JOIN order_items oi ON oi.order_id = o.id
             WHERE o.status != 'cancelled'{$date_where}
             GROUP BY DATE(o.created_at) ORDER BY tgl",
            $params
        );

        $top_products = $db->fetchAll(
            "SELECT p.name, SUM(oi.quantity) AS qty, SUM(oi.quantity * oi.price) AS revenue
             FROM order_items oi
             JOIN products p ON p.id = oi.product_id
             JOIN orders o ON o.id = oi.order_id
             WHERE o.status != 'cancelled'{$date_where}
             GROUP BY p.id ORDER BY revenue DESC LIMIT 15",
            $params
        );

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="laporan-penjualan-' . $from . '-sd-' . $to . '.csv"');
        echo "\xEF\xBB\xBF";

        $out = fopen('php://output', 'w');

        fputcsv($out, ['LAPORAN PENJUALAN GEPREK GEH', $from, 's/d', $to]);
        fputcsv($out, []);

        fputcsv($out, ['=== RINGKASAN HARIAN ===']);
        fputcsv($out, ['Tanggal', 'Pesanan', 'Qty', 'Omzet (Rp)']);
        foreach ($daily as $r) {
            fputcsv($out, [$r['tgl'], $r['order_count'], $r['qty'], $r['revenue']]);
        }
        fputcsv($out, []);

        fputcsv($out, ['=== PRODUK TERLARIS ===']);
        fputcsv($out, ['Produk', 'Qty Terjual', 'Omzet (Rp)']);
        foreach ($top_products as $r) {
            fputcsv($out, [$r['name'], $r['qty'], $r['revenue']]);
        }

        fclose($out);
        exit;
    }
}
