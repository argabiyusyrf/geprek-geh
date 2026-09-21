<?php
namespace Admin;
class StockController {

    public function index() {
        \Auth::requireStaff();
        $db = \Database::getInstance();
        $app = require __DIR__ . '/../../config/app.php';
        $threshold = (int) ($app['stock_low_threshold'] ?? 10);

        $q = trim((string) ($_GET['q'] ?? ''));
        $sort = $_GET['sort'] ?? 'stok';
        $allowed_sort = ['stok', 'nama', 'kategori'];
        if (!in_array($sort, $allowed_sort, true)) $sort = 'stok';

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $per = (int) ($_GET['per'] ?? 25);
        if (!in_array($per, [10, 15, 25, 50], true)) $per = 25;
        $offset = ($page - 1) * $per;

        $where = '1=1';
        $params = [];
        if ($q !== '') {
            $where .= " AND (p.name LIKE ? OR c.name LIKE ?)";
            $params[] = "%{$q}%";
            $params[] = "%{$q}%";
        }

        $kpis = \ProductRepo::stockKpi($threshold);

        $total = (int) \ProductRepo::publicCount($where, $params);
        $total_pages = max(1, (int) ceil($total / $per));
        if ($page > $total_pages) $page = $total_pages;

        $order_dir = [
            'stok'     => 'p.stock ASC',
            'nama'     => 'p.name ASC',
            'kategori' => 'c.name ASC, p.name ASC',
        ];
        $sort_sql = $order_dir[$sort];

        $products = \ProductRepo::adminList($where, $params, $sort_sql, $per, $offset);

        $admin_page_title = 'Manajemen Stok';
        render('admin/stock/index', get_defined_vars());
    }

    public function show($id) {
        \Auth::requireStaff();
        $db = \Database::getInstance();
        $app = require __DIR__ . '/../../config/app.php';
        $threshold = (int) ($app['stock_low_threshold'] ?? 10);
        $id = (int) $id;

        $product = \ProductRepo::byId($id);
        if (!$product) {
            \flash_set('error', 'Produk tidak ditemukan.');
            gg_redirect('/admin/stock');
            exit;
        }

        $movements = $db->fetchAll(
            "SELECT sm.*, u.name AS actor_name FROM stock_movements sm
             LEFT JOIN users u ON sm.user_id = u.id
             WHERE sm.product_id = ? ORDER BY sm.created_at DESC LIMIT 50",
            [$id]
        );

        $admin_page_title = 'Detail Stok: ' . $product['name'];
        render('admin/stock/show', get_defined_vars());
    }

    public function restock($id) {
        \Auth::requireStaff();
        if (!\verify_csrf()) {
            \flash_set('error', 'Token tidak valid.');
            gg_redirect('/admin/stock');
            exit;
        }
        $db = \Database::getInstance();
        $id = (int) $id;

        $product = $db->fetchOne("SELECT * FROM products WHERE id = ?", [$id]);
        if (!$product) {
            \flash_set('error', 'Produk tidak ditemukan.');
            gg_redirect('/admin/stock');
            exit;
        }

        $qty = (int) ($_POST['qty'] ?? 0);
        $note = trim($_POST['note'] ?? '');
        if ($qty <= 0) {
            \flash_set('error', 'Jumlah stok harus lebih dari 0.');
            gg_redirect("/admin/stock/{$id}");
            exit;
        }
        if ($qty > 99999) $qty = 99999;
        if (mb_strlen($note) > 255) $note = mb_substr($note, 0, 255);

        $new_stock = $product['stock'] + $qty;
        $db->query("UPDATE products SET stock = ? WHERE id = ?", [$new_stock, $id]);
        $db->insert('stock_movements', [
            'product_id' => $id,
            'qty_change' => $qty,
            'note'       => $note !== '' ? $note : null,
            'user_id'    => \Auth::id(),
        ]);

        \flash_set('success', "Stok {$product['name']} ditambah {$qty}. Total sekarang: {$new_stock}.");
        gg_redirect("/admin/stock/{$id}");
        exit;
    }
}
