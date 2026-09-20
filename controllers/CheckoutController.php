<?php
class CheckoutController {
    public function index() {
        Auth::requireLogin();
        $db = Database::getInstance();
        $user = Auth::user();
        $where = 'user_id';
        $val = Auth::id();
        $items = $db->fetchAll(
            "SELECT ct.*, p.name, p.slug, p.price, p.image, p.stock, c.name AS category_name
             FROM cart ct
             JOIN products p ON ct.product_id = p.id
             LEFT JOIN categories c ON p.category_id = c.id
             WHERE ct.{$where} = ? ORDER BY ct.created_at",
            [$val]
        );
        if (empty($items)) {
            flash_set('error', 'Keranjang kosong.');
            redirect('/geprek-geh/cart');
        }
        $promo = $_SESSION['promo'] ?? null;
        $app = require __DIR__ . '/../config/app.php';
        $proses = calculateOrderSummary($items, $promo);
        extract($proses);
        $total_qty = array_sum(array_map(fn($i) => $i['quantity'], $items));

        $payment_options = [];
        $bank_info = null;
        $ewallet_info = null;
        foreach (($app['payment']['methods'] ?? []) as $m) {
            $popt = [
                'label'  => $m['type'] === 'bank' ? 'Transfer ' . $m['name'] : 'E-Wallet ' . $m['name'],
                'icon'   => $m['type'] === 'bank' ? 'bank' : 'wallet',
                'type'   => $m['type'],
                'name'   => $m['name'],
                'number' => $m['number'],
                'holder' => $m['holder'],
                'desc'   => $m['description'] ?: ($m['type'] === 'bank'
                    ? 'Verifikasi manual oleh admin 1×24 jam'
                    : 'Verifikasi manual oleh admin dari bukti bayar'),
            ];
            $payment_options[$m['code']] = $popt;
            if ($m['type'] === 'bank' && !$bank_info) $bank_info = $popt;
            if ($m['type'] === 'ewallet' && !$ewallet_info) $ewallet_info = $popt;
        }
        $gateway = $app['payment']['gateway'] ?? ['type' => 'none', 'label' => 'QRIS', 'number' => ''];
        if (($gateway['type'] ?? 'none') === 'qris') {
            $glabel = trim((string) ($gateway['label'] ?? ''));
            $gnum = trim((string) ($gateway['number'] ?? ''));
            $payment_options['qris'] = [
                'label'  => 'QRIS' . ($glabel !== '' ? " · {$glabel}" : ''),
                'icon'   => 'wallet',
                'type'   => 'qris',
                'name'   => $glabel !== '' ? $glabel : 'QRIS',
                'number' => $gnum,
                'holder' => '',
                'desc'   => $gnum !== '' ? "Bayar via {$glabel}" . ' ' . $gnum . ', lalu kirim bukti bayar' : 'Scan kode QRIS, lalu kirim bukti bayar',
            ];
        }
        $payment_options['cod'] = [
            'label'  => 'Bayar di Tempat (COD)',
            'icon'   => 'cash',
            'type'   => 'cod',
            'name'   => 'COD',
            'number' => '',
            'holder' => '',
            'desc'   => 'Bayar tunai saat pesanan tiba',
        ];
        $default_pm = isset($payment_options['cod']) ? 'cod' : array_key_first($payment_options);
        $payment_details = ['bank' => $bank_info ?? ['name' => '-', 'number' => '-', 'holder' => '-'], 'ewallet' => $ewallet_info ?? ['name' => 'E-Wallet', 'number' => '-', 'holder' => '-']];
        $contacts = $app['contacts'] ?? [];

        $saved_addresses = user_addresses(Auth::id());

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

        render('checkout/index', get_defined_vars());
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
        $payment_method = $_POST['payment_method'] ?? '';
        // Valid: aktif di payment_methods (kode dinamis) atau 'qris' (jika gateway
        // QRIS aktif) atau 'cod'.
        $allowed_codes = [];
        foreach (($app['payment']['methods'] ?? []) as $m) $allowed_codes[] = $m['code'];
        $gateway = $app['payment']['gateway'] ?? ['type' => 'none'];
        if (($gateway['type'] ?? 'none') === 'qris') $allowed_codes[] = 'qris';
        $allowed_codes[] = 'cod';
        if (!in_array($payment_method, $allowed_codes, true)) {
            // Fallback legacy / invalid → metode aktif pertama, atau COD.
            $payment_method = $allowed_codes[0] ?? 'cod';
        }
        $pm_details = payment_method_details($payment_method); // false utk cod / tak dikenal
        $payment_method_id = $pm_details ? (int) $pm_details['id'] : null;
        if ($pm_details && $pm_details['type'] === 'cod') $payment_method_id = null;
        $notes = $post('notes');

        // Pick shipping address: radio `picked_address` works even without JS.
        // A numeric value loads the saved address from DB (ownership-verified);
        // 'manual' or absence falls back to the typed fields.
        $address_id = 0;
        $saved_source = null;
        $picked = trim((string) ($_POST['picked_address'] ?? ''));
        if ($picked !== '' && $picked !== 'manual') {
            $saved_source = $db->fetchOne(
                "SELECT * FROM addresses WHERE id = ? AND user_id = ?",
                [(int) $picked, Auth::id()]
            );
        }
        if (!$saved_source && !empty($_POST['address_id'])) {
            $saved_source = $db->fetchOne(
                "SELECT * FROM addresses WHERE id = ? AND user_id = ?",
                [(int) $_POST['address_id'], Auth::id()]
            );
        }
        if ($saved_source) {
            $address_id   = (int) $saved_source['id'];
            $recipient_name = $saved_source['recipient_name'];
            $phone        = $saved_source['phone'];
            $address      = $saved_source['address'];
            $province     = $saved_source['province'] ?? '';
            $city         = $saved_source['city'] ?? '';
            $district     = $saved_source['district'] ?? '';
            $village      = $saved_source['village'] ?? '';
            $postal_code  = $saved_source['postal_code'] ?? '';
        }

        $errors = [];
        if (empty($recipient_name)) {
            $errors['recipient_name'] = 'Nama penerima wajib diisi.';
        }
        if (empty($phone)) {
            $errors['phone'] = 'Nomor telepon wajib diisi.';
        } else {
            $phone = normalize_phone($phone);
            if (!valid_phone($phone)) {
                $errors['phone'] = 'Format nomor tidak valid. Contoh: 081234567890.';
            }
        }
        if (empty($address)) {
            $errors['address'] = 'Alamat pengiriman wajib diisi.';
        }
        if ($postal_code !== '' && !preg_match('/^\d{5}$/', $postal_code)) {
            $errors['postal_code'] = 'Kode pos harus 5 digit angka.';
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

        foreach ($items as $item) {
            if ($item['quantity'] > $item['stock']) {
                flash_set('error', "Stok {$item['name']} tidak cukup.");
                redirect('/geprek-geh/checkout');
            }
        }

        $promo = $_SESSION['promo'] ?? null;
        extract(calculateOrderSummary($items, $promo));

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
            'payment_method_id'     => $payment_method_id,
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

$reserved = [];
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
                // Stok habis saat checkout: batalkan order yang baru dibuat agar
                // tidak ada order/item yatim, dan kembalikan stok yang terreservasi.
                $db->delete('order_items', 'order_id = ?', [$order_id]);
                $db->delete('orders', 'id = ?', [$order_id]);
                foreach ($reserved as $r) {
                    $db->query("UPDATE products SET stock = stock + ? WHERE id = ?", [$r['qty'], $r['product_id']]);
                    stock_log($r['product_id'], (int) $r['qty'], "Pembatalan {$invoice} (stok habis saat checkout)");
                }
                flash_set('error', "Stok {$item['name']} habis saat checkout. Silakan periksa kembali.");
                redirect('/geprek-geh/cart');
            }
            stock_log($item['product_id'], -$item['quantity'], "Pesanan {$invoice} dibuat", Auth::id());
            $reserved[] = ['product_id' => $item['product_id'], 'qty' => $item['quantity']];
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
