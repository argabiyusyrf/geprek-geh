<?php
class CheckoutController {
    public function index() {
        Auth::requireLogin();
        $db = Database::getInstance();
        $user = Auth::user();
        $where = 'user_id';
        $val = Auth::id();
        $items = $db->fetchAll(
            "SELECT ct.*, p.name, p.slug, p.price, p.image, p.stock
             FROM cart ct JOIN products p ON ct.product_id = p.id
             WHERE ct.{$where} = ? ORDER BY ct.created_at",
            [$val]
        );
        if (empty($items)) {
            flash_set('error', 'Keranjang kosong.');
            redirect('/geprek-geh/cart');
        }
        $subtotal = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $items));
        $total_qty = array_sum(array_map(fn($i) => $i['quantity'], $items));
        $app = require __DIR__ . '/../config/app.php';
        $tax = (int)($subtotal * $app['tax_rate']);
        $shipping = $app['shipping'];
        $grand_total = $subtotal + $tax + $shipping;

        $payment_options = [
            'transfer' => ['label' => 'Transfer Bank', 'icon' => 'bank', 'desc' => 'Verifikasi manual oleh admin 1×24 jam'],
            'cod'      => ['label' => 'Bayar di Tempat (COD)', 'icon' => 'cash', 'desc' => 'Bayar tunai saat pesanan tiba'],
            'ewallet'  => ['label' => 'E-Wallet (ShopeePay)', 'icon' => 'wallet', 'desc' => 'Verifikasi manual oleh admin dari bukti bayar'],
        ];

        $payment_details = $app['payment'] ?? [];
        $contacts = $app['contacts'] ?? [];

        $saved_addresses = $db->fetchAll(
            "SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, updated_at DESC",
            [Auth::id()]
        );

        $old = $_SESSION['checkout_old'] ?? null;
        $recipient_name = $old['recipient_name'] ?? null;
        $phone = $old['phone'] ?? null;
        $address = $old['address'] ?? null;
        $payment_method = $old['payment_method'] ?? null;
        $notes = $old['notes'] ?? null;
        $field_errors = $_SESSION['checkout_errors'] ?? [];
        unset($_SESSION['checkout_errors'], $_SESSION['checkout_old']);

        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/checkout/index.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }

    public function process() {
        Auth::requireLogin();
        if (!verify_csrf()) {
            flash_set('error', 'Token tidak valid.');
            redirect('/geprek-geh/checkout');
        }

        $db = Database::getInstance();
        $user = Auth::user();
        $app = require __DIR__ . '/../config/app.php';

        $post = function ($key, $fallback = '') {
            return trim($_POST[$key] ?? $fallback);
        };
        $recipient_name = $post('recipient_name', $user['name'] ?? '');
        $phone = $post('phone', $user['phone'] ?? '');
        $address = $post('address', $user['address'] ?? '');
        $payment_method = $_POST['payment_method'] ?? 'transfer';
        if (!in_array($payment_method, ['transfer', 'ewallet', 'cod'], true)) $payment_method = 'transfer';
        $notes = $post('notes');

        $errors = [];
        if (empty($recipient_name)) {
            $errors['recipient_name'] = 'Nama penerima wajib diisi.';
        }
        if (empty($phone)) {
            $errors['phone'] = 'Nomor telepon wajib diisi.';
        } elseif (!preg_match('/^08\d{8,11}$/', $phone)) {
            $errors['phone'] = 'Format nomor tidak valid. Contoh: 081234567890.';
        }
        if (empty($address)) {
            $errors['address'] = 'Alamat pengiriman wajib diisi.';
        }
        if ($errors) {
            $_SESSION['checkout_old'] = ['recipient_name' => $recipient_name, 'phone' => $phone, 'address' => $address, 'payment_method' => $payment_method, 'notes' => $notes];
            $_SESSION['checkout_errors'] = $errors;
            redirect('/geprek-geh/checkout');
        }

        $items = $db->fetchAll(
            "SELECT ct.*, p.name, p.price, p.stock
             FROM cart ct JOIN products p ON ct.product_id = p.id
             WHERE ct.user_id = ? ORDER BY ct.created_at",
            [Auth::id()]
        );

        if (empty($items)) {
            flash_set('error', 'Keranjang kosong.');
            redirect('/geprek-geh/cart');
        }

        $subtotal = 0;
        foreach ($items as $item) {
            if ($item['quantity'] > $item['stock']) {
                flash_set('error', "Stok {$item['name']} tidak cukup.");
                redirect('/geprek-geh/checkout');
            }
            $subtotal += $item['price'] * $item['quantity'];
        }

        $tax = (int)($subtotal * $app['tax_rate']);
        $shipping = $app['shipping'];
        $grand_total = $subtotal + $tax + $shipping;

        $invoice = generate_invoice();
        $order_id = $db->insert('orders', [
            'user_id'          => Auth::id(),
            'invoice_no'       => $invoice,
            'total'            => $subtotal,
            'shipping_cost'    => $shipping,
            'tax'              => $tax,
            'grand_total'      => $grand_total,
            'status'           => 'pending',
            'payment_method'   => $payment_method,
            'shipping_address' => $address,
            'notes'            => $notes,
        ]);

        $db->update('users', ['phone' => $phone], 'id = ?', [Auth::id()]);
        unset($_SESSION['checkout_old']);

        foreach ($items as $item) {
            $db->insert('order_items', [
                'order_id'   => $order_id,
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
                'price'      => $item['price'],
            ]);
            $db->update('products', ['stock' => $item['stock'] - $item['quantity']], 'id = ?', [$item['product_id']]);
        }

        $db->delete('cart', 'user_id = ?', [Auth::id()]);
        order_log($db, $order_id, 'customer', 'Pesanan dibuat');

        NotificationController::push(
            Auth::id(),
            'order',
            "Pesanan {$invoice} dibuat",
            $payment_method === 'cod' ? 'Pesanan kamu sedang disiapkan. Bayar saat pesanan tiba.'
                                      : 'Pesanan kamu menunggu pembayaran.',
            "/geprek-geh/orders/{$order_id}"
        );
        $customer = Auth::user();
        NotificationController::pushToAdmins(
            'order',
            "Pesanan baru {$invoice}",
            'Dari ' . ($customer['name'] ?? 'Pelanggan') . '.',
            "/geprek-geh/admin/orders/{$order_id}"
        );

        flash_set('success', "Pesanan {$invoice} berhasil dibuat!");
        header("Location: /geprek-geh/orders/{$order_id}");
        exit;
    }
}
