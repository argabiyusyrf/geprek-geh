<?php
namespace Admin;
class ReviewController {

    public function index() {
        \Auth::requireAdmin();
        $db = \Database::getInstance();

        $status = $_GET['status'] ?? '';
        if (!in_array($status, ['visible', 'hidden'], true)) $status = '';

        $q = trim((string) ($_GET['q'] ?? ''));
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $per = (int) ($_GET['per'] ?? 15);
        if (!in_array($per, [10, 15, 25, 50], true)) $per = 15;
        $offset = ($page - 1) * $per;

        $where = '1=1';
        $params = [];
        if ($status === 'visible') { $where .= " AND pr.is_visible = 1"; }
        if ($status === 'hidden')  { $where .= " AND pr.is_visible = 0"; }
        if ($q !== '') {
            $like = '%' . $q . '%';
            $where .= " AND (u.name LIKE ? OR p.name LIKE ? OR pr.comment LIKE ?)";
            array_push($params, $like, $like, $like);
        }

        $kpis = $db->fetchOne(
            "SELECT COUNT(*) AS total,
                COALESCE(SUM(is_visible = 1), 0) AS visible,
                COALESCE(SUM(is_visible = 0), 0) AS hidden
             FROM product_reviews"
        );

        $total = (int) $db->fetchColumn(
            "SELECT COUNT(*) FROM product_reviews pr
             JOIN products p ON p.id = pr.product_id
             JOIN users u ON u.id = pr.user_id
             WHERE {$where}",
            $params
        );
        $total_pages = max(1, (int) ceil($total / $per));
        if ($page > $total_pages) $page = $total_pages;

        $reviews = $db->fetchAll(
            "SELECT pr.*, p.name AS product_name, p.slug AS product_slug, u.name AS user_name
             FROM product_reviews pr
             JOIN products p ON p.id = pr.product_id
             JOIN users u ON u.id = pr.user_id
             WHERE {$where} ORDER BY pr.created_at DESC LIMIT {$per} OFFSET {$offset}",
            $params
        );

        $admin_page_title = 'Moderasi Ulasan';
        require __DIR__ . '/../../views/layouts/admin-header.php';
        require __DIR__ . '/../../views/admin/reviews/index.php';
        require __DIR__ . '/../../views/layouts/admin-footer.php';
    }

    public function toggle($id) {
        \Auth::requireAdmin();
        $db = \Database::getInstance();
        $id = (int) $id;
        if (!\verify_csrf()) {
            \flash_set('error', 'Token tidak valid.');
            header('Location: /geprek-geh/admin/reviews');
            exit;
        }
        $review = $db->fetchOne("SELECT * FROM product_reviews WHERE id = ?", [$id]);
        if (!$review) {
            \flash_set('error', 'Ulasan tidak ditemukan.');
            header('Location: /geprek-geh/admin/reviews');
            exit;
        }
        $new = $review['is_visible'] ? 0 : 1;
        $db->update('product_reviews', ['is_visible' => $new], 'id = ?', [$id]);
        \flash_set('success', $new ? 'Ulasan ditampilkan kembali.' : 'Ulasan disembunyikan dari halaman publik.');
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?: '/geprek-geh/admin/reviews'));
        exit;
    }

    public function delete($id) {
        \Auth::requireAdmin();
        $db = \Database::getInstance();
        $id = (int) $id;
        if (!\verify_csrf()) {
            \flash_set('error', 'Token tidak valid.');
            header('Location: /geprek-geh/admin/reviews');
            exit;
        }
        $db->delete('product_reviews', 'id = ?', [$id]);
        \flash_set('success', 'Ulasan dihapus.');
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?: '/geprek-geh/admin/reviews'));
        exit;
    }
}