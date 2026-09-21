<?php
class OrderController {

    /** Ambil order milik user login; redirect ke daftar bila tak ditemukan. */
    private function findUserOrder($id) {
        $order = Database::getInstance()->fetchOne(
            "SELECT * FROM orders WHERE id = ? AND user_id = ?",
            [$id, Auth::id()]
        );
        if (!$order) {
            flash_set('error', 'Pesanan tidak ditemukan.');
            redirect('/orders');
        }
        return $order;
    }

    public function index() {
        Auth::requireLogin();
        $db = Database::getInstance();

        $allowed = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        $status = $_GET['status'] ?? '';
        if (!in_array($status, $allowed, true)) $status = '';

        $q = trim((string) ($_GET['q'] ?? ''));
        $sort = $_GET['sort'] ?? 'terbaru';
        $allowed_sort = ['terbaru', 'terlama', 'tertinggi', 'terendah'];
        if (!in_array($sort, $allowed_sort, true)) $sort = 'terbaru';

        $page = max(1, (int)($_GET['page'] ?? 1));
        $per_page = 8;
        $offset = ($page - 1) * $per_page;

        $where = 'user_id = ?';
        $params = [Auth::id()];
        if ($status !== '') {
            $where .= ' AND status = ?';
            $params[] = $status;
        }
        if ($q !== '') {
            $like = '%' . $q . '%';
            $where .= ' AND (invoice_no LIKE ? OR EXISTS (
                SELECT 1 FROM order_items oi JOIN products p ON p.id = oi.product_id
                WHERE oi.order_id = orders.id AND p.name LIKE ?
            ))';
            $params[] = $like;
            $params[] = $like;
        }

        $total = (int) $db->fetchColumn("SELECT COUNT(*) FROM orders WHERE {$where}", $params);
        $total_pages = max(1, (int) ceil($total / $per_page));
        if ($page > $total_pages) $page = $total_pages;

        $order_dir = [
            'terbaru'   => 'created_at DESC',
            'terlama'   => 'created_at ASC',
            'tertinggi' => '(total - discount + tax + shipping_cost) DESC',
            'terendah'  => '(total - discount + tax + shipping_cost) ASC',
        ][$sort];

        $orders = $db->fetchAll(
            "SELECT * FROM orders WHERE {$where} ORDER BY {$order_dir} LIMIT {$per_page} OFFSET {$offset}",
            $params
        );

        $order_items = [];
        if ($orders) {
            $ids = array_column($orders, 'id');
            $in = implode(',', array_fill(0, count($ids), '?'));
            $rows = $db->fetchAll(
                "SELECT oi.order_id, oi.quantity, oi.price, p.name, p.slug, p.image, c.name AS category_name
                 FROM order_items oi
                 JOIN products p ON oi.product_id = p.id
                 LEFT JOIN categories c ON p.category_id = c.id
                 WHERE oi.order_id IN ({$in})
                 ORDER BY oi.id ASC",
                $ids
            );
            foreach ($rows as $row) $order_items[$row['order_id']][] = $row;
        }

        $status_counts = [];
        $counts = $db->fetchAll(
            "SELECT status, COUNT(*) AS c FROM orders WHERE user_id = ? GROUP BY status",
            [Auth::id()]
        );
        foreach ($counts as $row) $status_counts[$row['status']] = (int) $row['c'];
        $all_count = array_sum($status_counts);

        render('orders/index', get_defined_vars());
    }

    public function show($id) {
        Auth::requireLogin();
        $db = Database::getInstance();
        $order = $this->findUserOrder($id);
        $items = order_items($id);
        $logs = $db->fetchAll(
            "SELECT * FROM order_logs WHERE order_id = ? ORDER BY created_at ASC, id ASC",
            [$id]
        );
        $app = require __DIR__ . '/../config/app.php';
        $contacts = $app['contacts'] ?? [];

        $pm_details = payment_method_details($order['payment_method']);
        $pm_type = payment_method_type($order['payment_method']);
        $payment_label = $pm_details && $pm_type === 'bank'
            ? 'Transfer ' . $pm_details['name']
            : payment_method_label($order['payment_method']);
        $payment_details = ['bank' => $pm_type === 'bank' && $pm_details ? $pm_details : null, 'ewallet' => $pm_type === 'ewallet' && $pm_details ? $pm_details : null];
        [$payment_status_label, $payment_badge] = format_payment_status($order['payment_status']);

        // Status timeline: created -> processing -> shipped -> delivered
        $timeline = [
            ['key' => 'created',    'label' => 'Pesanan Dibuat', 'desc' => 'Pesanan tercatat di sistem'],
            ['key' => 'processing', 'label' => 'Diproses Dapur', 'desc' => 'Sedang disiapkan tim dapur'],
            ['key' => 'shipped',    'label' => 'Sedang Dikirim', 'desc' => $order['tracking_no'] ? 'Nomor resi: ' . $order['tracking_no'] : 'Menuju alamat pengiriman'],
            ['key' => 'delivered',  'label' => 'Pesanan Diterima', 'desc' => 'Tiba & siap dinikmati'],
        ];
        $rank = ['created' => 0, 'processing' => 1, 'shipped' => 2, 'delivered' => 3];
        $current_rank = $rank[$order['status']] ?? 0;
        $cancelled = $order['status'] === 'cancelled';
        $total_qty = array_sum(array_map(fn($i) => $i['quantity'], $items));

        render('orders/show', get_defined_vars());
    }

    public function uploadProof($id) {
        Auth::requireLogin();
        if (!verify_csrf()) {
            flash_set('error', 'Token tidak valid.');
            redirect('/orders');
        }
        $db = Database::getInstance();
        $order = $this->findUserOrder($id);

        if (!in_array($order['status'], ['pending', 'processing'], true) || $order['payment_status'] !== 'unpaid') {
            flash_set('error', 'Bukti hanya bisa diupload untuk pesanan menunggu yang belum dibayar.');
            redirect('/orders/' . $order['id']);
        }
        if (!payment_requires_proof($order['payment_method'])) {
            flash_set('error', 'Pesanan COD tidak memerlukan upload bukti.');
            redirect('/orders/' . $order['id']);
        }

        if (!isset($_FILES['proof']) || $_FILES['proof']['error'] !== UPLOAD_ERR_OK) {
            flash_set('error', 'Gagal mengupload file. Pastikan kamu memilih file bukti.');
            redirect('/orders/' . $order['id']);
        }

        $file = $_FILES['proof'];
        $max_bytes = 2 * 1024 * 1024;
        $allowed_ext = ['png', 'jpg', 'jpeg', 'webp', 'heic', 'heif'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $mime = $file['type'];

        if ($file['size'] > $max_bytes) {
            flash_set('error', 'Ukuran file maksimal 2MB.');
            redirect('/orders/' . $order['id']);
        }
        if (!in_array($ext, $allowed_ext, true)) {
            flash_set('error', 'Format file tidak didukung. Gunakan PNG, JPG, WebP, atau HEIC.');
            redirect('/orders/' . $order['id']);
        }

        // Secondary check on the real mime from the uploaded content
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detected = $finfo ? finfo_file($finfo, $file['tmp_name']) : $mime;
        if ($finfo) finfo_close($finfo);
        $image_mimes = ['image/png', 'image/jpeg', 'image/webp', 'image/heic', 'image/heif', 'image/heic-sequence'];
        if (!in_array($detected, $image_mimes, true)) {
            flash_set('error', 'File yang diunggah bukan gambar yang valid.');
            redirect('/orders/' . $order['id']);
        }

        // Tertiary check: actually try to decode the image. This catches
        // polyglot files (e.g. PHP disguised as JPG) that pass mime sniffers.
        $imageInfo = @getimagesize($file['tmp_name']);
        $allowed_image_types = [IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_WEBP];
        if (!$imageInfo || !in_array($imageInfo[2], $allowed_image_types, true)) {
            flash_set('error', 'File yang diunggah bukan gambar yang valid.');
            redirect('/orders/' . $order['id']);
        }

        // Verify declared extension matches detected image type
        $typeToExt = [IMAGETYPE_PNG => 'png', IMAGETYPE_JPEG => 'jpg', IMAGETYPE_WEBP => 'webp'];
        $realExt = $typeToExt[$imageInfo[2]] ?? null;
        if (!$realExt || !in_array($ext, [$realExt, $realExt === 'jpg' ? 'jpeg' : $realExt], true)) {
            flash_set('error', 'Ekstensi file tidak cocok dengan isi gambar.');
            redirect('/orders/' . $order['id']);
        }

        $filename = 'proof_' . $order['id'] . '_' . time() . '.' . $ext;
        $upload_dir = __DIR__ . '/../assets/uploads/payments/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
            $payment_bank = trim($_POST['payment_bank'] ?? '');
            $payment_account_no = trim($_POST['payment_account_no'] ?? '');
            $payment_account_name = trim($_POST['payment_account_name'] ?? '');

            $db->update('orders', [
                'payment_proof'        => $filename,
                'payment_bank'         => $payment_bank ?: null,
                'payment_account_no'   => $payment_account_no ?: null,
                'payment_account_name' => $payment_account_name ?: null,
            ], 'id = ?', [$id]);
            order_log($db, $id, 'customer', 'Bukti pembayaran diunggah, menunggu verifikasi admin');
            NotificationController::pushToAdmins(
                'payment',
                "Bukti bayar baru — {$order['invoice_no']}",
                'Menunggu verifikasi pembayaran.',
                "/admin/orders/{$id}"
            );
            flash_set('success', 'Bukti pembayaran berhasil diupload.');
        } else {
            flash_set('error', 'Gagal menyimpan file. Silakan coba lagi.');
        }
        redirect('/orders/' . $order['id']);
    }

    public function cancel($id) {
        Auth::requireLogin();
        if (!verify_csrf()) {
            flash_set('error', 'Token tidak valid.');
            redirect('/orders');
        }
        $db = Database::getInstance();
        $order = $this->findUserOrder($id);

        if ($order['status'] !== 'pending') {
            flash_set('error', 'Pesanan hanya bisa dibatalkan selama masih berstatus "Menunggu".');
            redirect('/orders/' . $id);
        }

        $reason = trim($_POST['cancel_reason'] ?? '');
        if ($reason === '') {
            flash_set('error', 'Alasan pembatalan wajib diisi.');
            redirect('/orders/' . $id);
        }

        $data = ['status'        => 'cancelled',
                 'cancel_reason' => mb_substr($reason, 0, 255)];
        if ($order['payment_status'] === 'paid') {
            $data['payment_status'] = 'refunded';
        }
        $db->update('orders', $data, 'id = ?', [$id]);
        order_restore_stock($db, $id);
        order_log($db, $id, 'customer', 'Pesanan dibatalkan oleh pembeli — alasan: ' . mb_substr($reason, 0, 150)
            . ($order['payment_status'] === 'paid' ? ', pembayaran di-refund' : ', stok dikembalikan'));
        $refund_note = $order['payment_status'] === 'paid' ? ' Pembayaran yang sudah lunas akan di-refund.' : '';
        NotificationController::pushToAdmins(
            'order',
            "Pesanan {$order['invoice_no']} dibatalkan",
            'Oleh ' . ($_SESSION['user_name'] ?? 'Pelanggan') . '. Alasan: ' . mb_substr($reason, 0, 180) . '. Stok dikembalikan.' . $refund_note,
            "/admin/orders/{$id}"
        );

        order_status_email(Auth::id(), $order['invoice_no'], 'Dibatalkan',
            'Pesanan dibatalkan. Stok dikembalikan.' . $refund_note);

        flash_set('success', 'Pesanan berhasil dibatalkan.'
            . ($order['payment_status'] === 'paid' ? ' Pembayaran akan di-refund.' : ' Stok telah dikembalikan.'));
        redirect('/orders/' . $id);
    }

    public function receive($id) {
        Auth::requireLogin();
        if (!verify_csrf()) {
            flash_set('error', 'Token tidak valid.');
            redirect('/orders');
        }
        $db = Database::getInstance();
        $order = $this->findUserOrder($id);

        if ($order['status'] !== 'shipped') {
            flash_set('error', 'Pesanan hanya bisa diselesaikan setelah statusnya "Sedang Dikirim".');
            redirect('/orders/' . $id);
        }

        $data = ['status' => 'delivered'];
        if (payment_method_type($order['payment_method']) === 'cod') {
            $data['payment_status'] = 'paid';
        }
        $db->update('orders', $data, 'id = ?', [$id]);
        order_log(
            $db,
            $id,
            'customer',
            'Pesanan selesai — dikonfirmasi diterima'
            . (payment_method_type($order['payment_method']) === 'cod' ? ' (pembayaran COD diterima saat antar)' : '')
        );
        NotificationController::push(
            Auth::id(),
            'order',
            "Pesanan {$order['invoice_no']} selesai",
            'Terima kasih sudah berbelanja di Geprek Geh.',
            "/orders/{$id}"
        );
        NotificationController::pushToAdmins(
            'order',
            "Pesanan {$order['invoice_no']} dikonfirmasi diterima",
            'Dikonsumsi pembeli. Pesanan selesai.',
            "/admin/orders/{$id}"
        );

        order_status_email(Auth::id(), $order['invoice_no'], 'Selesai',
            'Terima kasih sudah berbelanja di Geprek Geh!');

        flash_set('success', 'Terima kasih! Pesanan ditandai selesai.');
        redirect('/orders/' . $id);
    }

    public function reorder($id) {
        Auth::requireLogin();
        if (!verify_csrf()) {
            flash_set('error', 'Token tidak valid.');
            redirect('/orders');
        }
        $db = Database::getInstance();
        $order = $this->findUserOrder($id);

        $items = $db->fetchAll(
            "SELECT oi.product_id, oi.quantity, p.name, p.stock
             FROM order_items oi JOIN products p ON oi.product_id = p.id
             WHERE oi.order_id = ?",
            [$id]
        );
        if (empty($items)) {
            flash_set('error', 'Tidak ada item untuk diulang.');
            redirect('/orders/' . $id);
        }

        $added = 0;
        $skipped = [];
        foreach ($items as $item) {
            $existing = $db->fetchOne(
                "SELECT * FROM cart WHERE user_id = ? AND product_id = ?",
                [Auth::id(), $item['product_id']]
            );
            $current = $existing ? (int) $existing['quantity'] : 0;
            if ($current + $item['quantity'] > $item['stock']) {
                $skipped[] = $item['name'];
                continue;
            }
            if ($existing) {
                $db->update('cart', ['quantity' => $current + $item['quantity']], 'id = ?', [$existing['id']]);
            } else {
                $db->insert('cart', [
                    'user_id'    => Auth::id(),
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                ]);
            }
            $added++;
        }

        if ($added > 0) {
            flash_set('success', "{$added} item ditambahkan ke keranjang dari pesanan {$order['invoice_no']}."
                . ($skipped ? ' (' . implode(', ', $skipped) . ' dilewati karena stok tidak cukup).' : ''));
        } else {
            flash_set('error', 'Tidak ada item yang bisa diulang karena stok tidak cukup.');
        }
        redirect('/cart');
    }

    /**
     * Sajikan bukti pembayaran via endpoint berizin (pemilik order / admin-staff),
     * bukan file publik langsung. Nama file tak pernah dibocorkan; gambar dikirim
     * dengan header yang aman.
     */
    public function paymentProof($id) {
        Auth::requireLogin();
        $isStaff = Auth::staff();
        $row = $isStaff
            ? Database::getInstance()->fetchOne(
                "SELECT id, payment_proof FROM orders WHERE id = ?",
                [(int) $id]
            )
            : Database::getInstance()->fetchOne(
                "SELECT id, payment_proof FROM orders WHERE id = ? AND user_id = ?",
                [(int) $id, Auth::id()]
            );

        if (!$row || empty($row['payment_proof'])) {
            http_response_code(404);
            exit('Bukti pembayaran tidak ditemukan.');
        }

        $file = dirname(__DIR__) . '/assets/uploads/payments/' . basename((string) $row['payment_proof']);
        if (!is_file($file) || !is_readable($file)) {
            http_response_code(404);
            exit('File bukti pembayaran tidak tersedia.');
        }

        $mime = mime_content_type($file) ?: 'application/octet-stream';
        if (!str_starts_with($mime, 'image/')) {
            $mime = 'application/octet-stream';
        }

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($file));
        header('Content-Disposition: inline; filename="proof"');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, max-age=300');
        readfile($file);
        exit;
    }
}
