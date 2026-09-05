<?php
class OrderController {
    public function index() {
        Auth::requireLogin();
        $db = Database::getInstance();

        $allowed = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        $status = $_GET['status'] ?? '';
        if (!in_array($status, $allowed, true)) $status = '';

        $page = max(1, (int)($_GET['page'] ?? 1));
        $per_page = 8;
        $offset = ($page - 1) * $per_page;

        $where = 'user_id = ?';
        $params = [Auth::id()];
        if ($status !== '') {
            $where .= ' AND status = ?';
            $params[] = $status;
        }

        $total = (int) $db->fetchColumn("SELECT COUNT(*) FROM orders WHERE {$where}", $params);
        $total_pages = max(1, ceil($total / $per_page));
        if ($page > $total_pages) $page = $total_pages;

        $orders = $db->fetchAll(
            "SELECT * FROM orders WHERE {$where} ORDER BY created_at DESC LIMIT {$per_page} OFFSET {$offset}",
            $params
        );

        $status_counts = [];
        $counts = $db->fetchAll(
            "SELECT status, COUNT(*) AS c FROM orders WHERE user_id = ? GROUP BY status",
            [Auth::id()]
        );
        foreach ($counts as $row) $status_counts[$row['status']] = (int) $row['c'];
        $all_count = array_sum($status_counts);

        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/orders/index.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }

    public function show($id) {
        Auth::requireLogin();
        $db = Database::getInstance();
        $order = $db->fetchOne(
            "SELECT * FROM orders WHERE id = ? AND user_id = ?",
            [$id, Auth::id()]
        );
        if (!$order) {
            flash_set('error', 'Pesanan tidak ditemukan.');
            redirect('/geprek-geh/orders');
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
        $app = require __DIR__ . '/../config/app.php';
        $payment_details = $app['payment'] ?? [];
        $contacts = $app['contacts'] ?? [];

        $payment_labels = [
            'transfer' => 'Transfer Bank',
            'cod'      => 'Bayar di Tempat (COD)',
            'ewallet'  => 'E-Wallet (ShopeePay)',
        ];
        $payment_label = $payment_labels[$order['payment_method']] ?? ucfirst($order['payment_method']);
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

        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/orders/show.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }

    public function uploadProof($id) {
        Auth::requireLogin();
        if (!verify_csrf()) {
            flash_set('error', 'Token tidak valid.');
            redirect('/geprek-geh/orders');
        }
        $db = Database::getInstance();
        $order = $db->fetchOne(
            "SELECT * FROM orders WHERE id = ? AND user_id = ?",
            [$id, Auth::id()]
        );
        if (!$order) {
            flash_set('error', 'Pesanan tidak ditemukan.');
            redirect('/geprek-geh/orders');
        }

        if ($order['status'] !== 'pending' || $order['payment_status'] !== 'unpaid') {
            flash_set('error', 'Bukti hanya bisa diupload untuk pesanan menunggu yang belum dibayar.');
            redirect('/geprek-geh/orders/' . $order['id']);
        }
        if (!in_array($order['payment_method'], ['transfer', 'ewallet'], true)) {
            flash_set('error', 'Pesanan COD tidak memerlukan upload bukti.');
            redirect('/geprek-geh/orders/' . $order['id']);
        }

        if (!isset($_FILES['proof']) || $_FILES['proof']['error'] !== UPLOAD_ERR_OK) {
            flash_set('error', 'Gagal mengupload file. Pastikan kamu memilih file bukti.');
            redirect('/geprek-geh/orders/' . $order['id']);
        }

        $file = $_FILES['proof'];
        $max_bytes = 2 * 1024 * 1024;
        $allowed_ext = ['png', 'jpg', 'jpeg', 'webp', 'heic', 'heif'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $mime = $file['type'];

        if ($file['size'] > $max_bytes) {
            flash_set('error', 'Ukuran file maksimal 2MB.');
            redirect('/geprek-geh/orders/' . $order['id']);
        }
        if (!in_array($ext, $allowed_ext, true)) {
            flash_set('error', 'Format file tidak didukung. Gunakan PNG, JPG, WebP, atau HEIC.');
            redirect('/geprek-geh/orders/' . $order['id']);
        }

        // Secondary check on the real mime from the uploaded content
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detected = $finfo ? finfo_file($finfo, $file['tmp_name']) : $mime;
        if ($finfo) finfo_close($finfo);
        $image_mimes = ['image/png', 'image/jpeg', 'image/webp', 'image/heic', 'image/heif', 'image/heic-sequence'];
        if (!in_array($detected, $image_mimes, true)) {
            flash_set('error', 'File yang diunggah bukan gambar yang valid.');
            redirect('/geprek-geh/orders/' . $order['id']);
        }

        $filename = 'proof_' . $order['id'] . '_' . time() . '.' . $ext;
        $upload_dir = __DIR__ . '/../assets/uploads/payments/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
            $db->update('orders', ['payment_proof' => $filename], 'id = ?', [$id]);
            order_log($db, $id, 'customer', 'Bukti pembayaran diunggah, menunggu verifikasi admin');
            NotificationController::pushToAdmins(
                'payment',
                "Bukti bayar baru — {$order['invoice_no']}",
                'Menunggu verifikasi pembayaran.',
                "/geprek-geh/admin/orders/{$id}"
            );
            flash_set('success', 'Bukti pembayaran berhasil diupload.');
        } else {
            flash_set('error', 'Gagal menyimpan file. Silakan coba lagi.');
        }
        redirect('/geprek-geh/orders/' . $order['id']);
    }

    public function cancel($id) {
        Auth::requireLogin();
        if (!verify_csrf()) {
            flash_set('error', 'Token tidak valid.');
            redirect('/geprek-geh/orders');
        }
        $db = Database::getInstance();
        $order = $db->fetchOne(
            "SELECT * FROM orders WHERE id = ? AND user_id = ?",
            [$id, Auth::id()]
        );
        if (!$order) {
            flash_set('error', 'Pesanan tidak ditemukan.');
            redirect('/geprek-geh/orders');
        }

        if (!in_array($order['status'], ['pending', 'processing'], true)) {
            flash_set('error', 'Pesanan tidak dapat dibatalkan karena statusnya "' . $order['status'] . '".');
            redirect('/geprek-geh/orders/' . $id);
        }

        $data = ['status'      => 'cancelled',
                 'cancel_reason' => 'Dibatalkan oleh pembeli'];
        if ($order['payment_status'] === 'paid') {
            $data['payment_status'] = 'refunded';
        }
        $db->update('orders', $data, 'id = ?', [$id]);
        order_restore_stock($db, $id);
        order_log($db, $id, 'customer', 'Pesanan dibatalkan oleh pembeli — stok dikembalikan'
            . ($order['payment_status'] === 'paid' ? ', pembayaran di-refund' : ''));
        $refund_note = $order['payment_status'] === 'paid' ? ' Pembayaran yang sudah lunas akan di-refund.' : '';
        NotificationController::pushToAdmins(
            'order',
            "Pesanan {$order['invoice_no']} dibatalkan",
            'Oleh ' . ($_SESSION['user_name'] ?? 'Pelanggan') . '. Stok dikembalikan.' . $refund_note,
            "/geprek-geh/admin/orders/{$id}"
        );
        flash_set('success', 'Pesanan berhasil dibatalkan.'
            . ($order['payment_status'] === 'paid' ? ' Pembayaran akan di-refund.' : ' Stok telah dikembalikan.'));
        redirect('/geprek-geh/orders/' . $id);
    }

    public function receive($id) {
        Auth::requireLogin();
        if (!verify_csrf()) {
            flash_set('error', 'Token tidak valid.');
            redirect('/geprek-geh/orders');
        }
        $db = Database::getInstance();
        $order = $db->fetchOne(
            "SELECT * FROM orders WHERE id = ? AND user_id = ?",
            [$id, Auth::id()]
        );
        if (!$order) {
            flash_set('error', 'Pesanan tidak ditemukan.');
            redirect('/geprek-geh/orders');
        }

        if ($order['status'] !== 'shipped') {
            flash_set('error', 'Pesanan hanya bisa diselesaikan setelah statusnya "Sedang Dikirim".');
            redirect('/geprek-geh/orders/' . $id);
        }

        $data = ['status' => 'delivered'];
        if ($order['payment_method'] === 'cod') {
            $data['payment_status'] = 'paid';
        }
        $db->update('orders', $data, 'id = ?', [$id]);
        order_log(
            $db,
            $id,
            'customer',
            'Pesanan selesai — dikonfirmasi diterima'
            . ($order['payment_method'] === 'cod' ? ' (pembayaran COD diterima saat antar)' : '')
        );
        NotificationController::push(
            Auth::id(),
            'order',
            "Pesanan {$order['invoice_no']} selesai",
            'Terima kasih sudah berbelanja di Geprek Geh.',
            "/geprek-geh/orders/{$id}"
        );
        NotificationController::pushToAdmins(
            'order',
            "Pesanan {$order['invoice_no']} dikonfirmasi diterima",
            'Dikonsumsi pembeli. Pesanan selesai.',
            "/geprek-geh/admin/orders/{$id}"
        );
        flash_set('success', 'Terima kasih! Pesanan ditandai selesai.');
        redirect('/geprek-geh/orders/' . $id);
    }

    public function reorder($id) {
        Auth::requireLogin();
        if (!verify_csrf()) {
            flash_set('error', 'Token tidak valid.');
            redirect('/geprek-geh/orders');
        }
        $db = Database::getInstance();
        $order = $db->fetchOne(
            "SELECT * FROM orders WHERE id = ? AND user_id = ?",
            [$id, Auth::id()]
        );
        if (!$order) {
            flash_set('error', 'Pesanan tidak ditemukan.');
            redirect('/geprek-geh/orders');
        }

        $items = $db->fetchAll(
            "SELECT oi.product_id, oi.quantity, p.name, p.stock
             FROM order_items oi JOIN products p ON oi.product_id = p.id
             WHERE oi.order_id = ?",
            [$id]
        );
        if (empty($items)) {
            flash_set('error', 'Tidak ada item untuk diulang.');
            redirect('/geprek-geh/orders/' . $id);
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
        redirect('/geprek-geh/cart');
    }
}
