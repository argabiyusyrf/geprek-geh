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

        $promo = $_SESSION['promo'] ?? null;
        $discount = 0;
        $promo_label = '';
        if ($promo && $subtotal > 0) {
            $d = PromoController::calcDiscount($promo, $subtotal);
            $discount = $d['discount'];
            $promo_label = $d['label'];
        }

        $grand_total = max(0, $subtotal - $discount + $tax + $shipping);

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
        $province = $old['province'] ?? null;
        $city = $old['city'] ?? null;
        $district = $old['district'] ?? null;
        $village = $old['village'] ?? null;
        $postal_code = $old['postal_code'] ?? null;
        $payment_method = $old['payment_method'] ?? null;
        $notes = $old['notes'] ?? null;
        $selected_address_id = $old['address_id'] ?? null;
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
        $address = $post('address', '');
        $province = $post('province');
        $city = $post('city');
        $district = $post('district');
        $village = $post('village');
        $postal_code = $post('postal_code');
        $address_id = (int) ($_POST['address_id'] ?? 0);
        $payment_method = $_POST['payment_method'] ?? 'transfer';
        if (!in_array($payment_method, ['transfer', 'ewallet', 'cod'], true)) $payment_method = 'transfer';
        $notes = $post('notes');

        $errors = [];
        if (empty($recipient_name)) {
            $errors['recipient_name'] = 'Nama penerima wajib diisi.';
        }
        if (empty($phone)) {
            $errors['phone'] = 'Nomor telepon wajib diisi.';
        } else {
            $phone_digits = preg_replace('/\D/', '', $phone);
            if (str_starts_with($phone_digits, '62')) $phone_digits = '0' . substr($phone_digits, 2);
            $phone = $phone_digits;
            if (!preg_match('/^08\d{8,11}$/', $phone_digits)) {
                $errors['phone'] = 'Format nomor tidak valid. Contoh: 081234567890.';
            }
        }
        if (empty($address)) {
            $errors['address'] = 'Alamat pengiriman wajib diisi.';
        }
        if ($errors) {
            $_SESSION['checkout_old'] = ['recipient_name' => $recipient_name, 'phone' => $phone, 'address' => $address, 'province' => $province, 'city' => $city, 'district' => $district, 'village' => $village, 'postal_code' => $postal_code, 'address_id' => $address_id, 'payment_method' => $payment_method, 'notes' => $notes];
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

        $promo = $_SESSION['promo'] ?? null;
        $discount = 0;
        if ($promo && $subtotal > 0) {
            $d = PromoController::calcDiscount($promo, $subtotal);
            $discount = $d['discount'];
        }

        $grand_total = max(0, $subtotal - $discount + $tax + $shipping);

        $invoice = generate_invoice();
        $full_address = array_filter([
            $address,
            $village, $district, $city, $province,
            $postal_code ? "Kode Pos {$postal_code}" : null,
        ], fn($v) => !empty($v));
        $order_address = implode(', ', $full_address);

        $order_id = $db->insert('orders', [
            'user_id'               => Auth::id(),
            'shipping_address_id'   => $address_id ?: null,
            'invoice_no'            => $invoice,
            'total'                 => $subtotal,
            'discount'              => $discount,
            'promo_code'            => $promo['code'] ?? null,
            'promo_code_id'         => $promo['id'] ?? null,
            'shipping_cost'         => $shipping,
            'tax'                   => $tax,
            'status'                => 'pending',
            'payment_method'        => $payment_method,
            'shipping_address'      => $order_address,
            'notes'                 => $notes,
        ]);

        // Increment promo usage
        if ($promo && $discount > 0) {
            $db->query("UPDATE promo_codes SET used_count = used_count + 1 WHERE id = ?", [$promo['id']]);
        }
        unset($_SESSION['promo']);

        $db->update('users', ['phone' => $phone], 'id = ?', [Auth::id()]);
        unset($_SESSION['checkout_old']);

        foreach ($items as $item) {
            $db->insert('order_items', [
                'order_id'   => $order_id,
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
                'price'      => $item['price'],
            ]);
            $result = $db->query(
                "UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?",
                [$item['quantity'], $item['product_id'], $item['quantity']]
            );
            if ($result->rowCount() === 0) {
                flash_set('error', "Stok {$item['name']} habis saat checkout. Silakan periksa kembali.");
                redirect('/geprek-geh/cart');
            }
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

        // Transactional email — order created (best-effort, never blocks order success)
        try {
            if (!empty($customer['email']) && (int)($customer['notify_email'] ?? 1) === 1) {
                Mail::orderCreated($customer['email'], $customer['name'], [
                    'invoice_no'  => $invoice,
                    'grand_total' => $grand_total,
                ], $items);
            }
        } catch (Exception $e) {
            error_log('[Checkout] order email failed: ' . $e->getMessage());
        }

        flash_set('success', "Pesanan {$invoice} berhasil dibuat!");
        header("Location: /geprek-geh/orders/{$order_id}");
        exit;
    }
}
