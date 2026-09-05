<?php
class CartController {
    private function sessionId() {
        if (empty($_SESSION['cart_session'])) {
            $_SESSION['cart_session'] = session_id();
        }
        return $_SESSION['cart_session'];
    }

    public function index() {
        $db = Database::getInstance();
        $items = $this->getItems($db);
        $subtotal = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $items));
        $app = require __DIR__ . '/../config/app.php';
        $tax = (int)($subtotal * $app['tax_rate']);
        $shipping = $app['shipping'];
        $grand_total = $subtotal + $tax + $shipping;

        require __DIR__ . '/../views/layouts/header.php';
        require __DIR__ . '/../views/cart/index.php';
        require __DIR__ . '/../views/layouts/footer.php';
    }

    public function add() {
        if (!verify_csrf()) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['ok' => false, 'message' => 'Token tidak valid.']);
                exit;
            }
            flash_set('error', 'Token tidak valid.');
            redirect('/geprek-geh/products');
        }
        $db = Database::getInstance();
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        $respond = function (bool $ok, string $msg, array $extra = []) use ($isAjax) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(array_merge(['ok' => $ok, 'message' => $msg], $extra));
                exit;
            }
            flash_set($ok ? 'success' : 'error', $msg);
        };

        $product_id = (int)($_POST['product_id'] ?? 0);
        $qty = max(1, (int)($_POST['quantity'] ?? 1));

        if (!$product_id) {
            $respond(false, 'Produk tidak ditemukan.');
            redirect('/geprek-geh/products');
        }

        $product = $db->fetchOne("SELECT * FROM products WHERE id = ? AND is_active = 1", [$product_id]);
        if (!$product) {
            $respond(false, 'Produk tidak ditemukan.');
            header('Location: /geprek-geh/products');
            exit;
        }

        if ($product['stock'] < $qty) {
            $respond(false, 'Stok tidak cukup.');
            header("Location: /geprek-geh/products/{$product['slug']}");
            exit;
        }

        $where_col = Auth::check() ? 'user_id' : 'session_id';
        $where_val = Auth::check() ? Auth::id() : $this->sessionId();

        $existing = $db->fetchOne(
            "SELECT * FROM cart WHERE {$where_col} = ? AND product_id = ?",
            [$where_val, $product_id]
        );

        if ($existing) {
            $new_qty = $existing['quantity'] + $qty;
            if ($new_qty > $product['stock']) $new_qty = $product['stock'];
            $db->update('cart', ['quantity' => $new_qty], 'id = ?', [$existing['id']]);
        } else {
            $data = [
                'product_id' => $product_id,
                'quantity'   => $qty,
            ];
            if (Auth::check()) {
                $data['user_id'] = Auth::id();
            } else {
                $data['session_id'] = $this->sessionId();
            }
            $db->insert('cart', $data);
        }

        $respond(true, 'Produk ditambahkan ke keranjang.', ['count' => CartController::count()]);
        header('Location: /geprek-geh/cart');
        exit;
    }

    public function update() {
        if (!verify_csrf()) {
            flash_set('error', 'Token tidak valid.');
            redirect('/geprek-geh/cart');
        }
        $db = Database::getInstance();
        $cart_id = (int)($_POST['cart_id'] ?? 0);
        $qty = max(1, (int)($_POST['quantity'] ?? 1));

        $where_col = Auth::check() ? 'user_id' : 'session_id';
        $where_val = Auth::check() ? Auth::id() : $this->sessionId();
        $row = $db->fetchOne(
            "SELECT ct.*, p.stock FROM cart ct JOIN products p ON ct.product_id = p.id WHERE ct.id = ? AND ct.{$where_col} = ?",
            [$cart_id, $where_val]
        );
        if ($row) {
            if ($qty > $row['stock']) {
                flash_set('error', 'Stok tidak cukup.');
            } else {
                $db->update('cart', ['quantity' => $qty], 'id = ?', [$cart_id]);
                flash_set('success', 'Keranjang diperbarui.');
            }
        }
        header('Location: /geprek-geh/cart');
        exit;
    }

    public function remove() {
        if (!verify_csrf()) {
            flash_set('error', 'Token tidak valid.');
            redirect('/geprek-geh/cart');
        }
        $db = Database::getInstance();
        $cart_id = (int)($_POST['cart_id'] ?? 0);
        $where_col = Auth::check() ? 'user_id' : 'session_id';
        $where_val = Auth::check() ? Auth::id() : $this->sessionId();
        $db->delete('cart', 'id = ? AND ' . $where_col . ' = ?', [$cart_id, $where_val]);
        flash_set('success', 'Produk dihapus dari keranjang.');
        header('Location: /geprek-geh/cart');
        exit;
    }

    public function clear() {
        $db = Database::getInstance();
        $where_col = Auth::check() ? 'user_id' : 'session_id';
        $where_val = Auth::check() ? Auth::id() : $this->sessionId();
        $db->delete('cart', $where_col . ' = ?', [$where_val]);
        flash_set('success', 'Keranjang telah dikosongkan.');
        header('Location: /geprek-geh/cart');
        exit;
    }

    private function getItems($db) {
        $where_col = Auth::check() ? 'user_id' : 'session_id';
        $where_val = Auth::check() ? Auth::id() : $this->sessionId();
        return $db->fetchAll(
            "SELECT ct.*, p.name, p.slug, p.price, p.image, p.stock, c.name AS category_name
             FROM cart ct JOIN products p ON ct.product_id = p.id
             JOIN categories c ON p.category_id = c.id
             WHERE ct.{$where_col} = ? ORDER BY ct.created_at",
            [$where_val]
        );
    }

    public static function count() {
        $db = Database::getInstance();
        $where_col = Auth::check() ? 'user_id' : 'session_id';
        $where_val = Auth::check() ? Auth::id() : session_id();
        return (int) $db->fetchColumn(
            "SELECT COALESCE(SUM(quantity),0) FROM cart WHERE {$where_col} = ?",
            [$where_val]
        );
    }
}
