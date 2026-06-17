<?php
// pages/cart.php
require_once __DIR__ . '/../includes/config.php';

// Handle Cart Updates or Removes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

    if ($action === 'update') {
        $qty = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        if ($qty < 1) $qty = 1;
        
        // Fetch product stock to limit quantity
        $stmt = $pdo->prepare("SELECT stock FROM products WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $stock = $stmt->fetchColumn();

        if ($qty > $stock) {
            $_SESSION['cart'][$product_id] = $stock;
            $_SESSION['flash_message'] = [
                'type' => 'warning',
                'text' => 'Jumlah beli disesuaikan dengan stok yang ada.'
            ];
        } else {
            $_SESSION['cart'][$product_id] = $qty;
        }
    } elseif ($action === 'remove') {
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
        }
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'text' => 'Produk dihapus dari keranjang.'
        ];
    }
    header("Location: /jp-annahls/pages/cart.php");
    exit();
}

// Handle Checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    // Requires login to checkout
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['flash_message'] = [
            'type' => 'warning',
            'text' => 'Silakan masuk (login) terlebih dahulu untuk melanjutkan pembelian.'
        ];
        header("Location: /jp-annahls/pages/login.php");
        exit();
    }

    if (empty($_SESSION['cart'])) {
        $_SESSION['flash_message'] = [
            'type' => 'danger',
            'text' => 'Keranjang belanja Anda kosong.'
        ];
        header("Location: /jp-annahls/pages/cart.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $notes = trim($_POST['notes'] ?? '');
    
    // Fetch product details for validation and total calculation
    $product_ids = array_keys($_SESSION['cart']);
    $in_placeholder = implode(',', array_fill(0, count($product_ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id IN ($in_placeholder) AND is_active = 1");
    $stmt->execute($product_ids);
    $products = $stmt->fetchAll();

    $grand_total = 0;
    $order_items_to_insert = [];

    foreach ($products as $prod) {
        $p_id = $prod['product_id'];
        $qty = $_SESSION['cart'][$p_id];
        
        // Final stock check
        if ($qty > $prod['stock']) {
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'text' => 'Stok produk ' . esc($prod['product_name']) . ' tidak cukup. Pembelian dibatalkan.'
            ];
            header("Location: /jp-annahls/pages/cart.php");
            exit();
        }

        $subtotal = $prod['harga_jual'] * $qty;
        $grand_total += $subtotal;

        $order_items_to_insert[] = [
            'product_id' => $p_id,
            'quantity' => $qty,
            'unit_price' => $prod['harga_jual'],
            'subtotal' => $subtotal,
            'new_stock' => $prod['stock'] - $qty
        ];
    }

    // DB Transaction for Checkout
    try {
        $pdo->beginTransaction();

        // 1. Insert order record
        $order_stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, status, notes) VALUES (?, ?, 'pending', ?)");
        $order_stmt->execute([$user_id, $grand_total, $notes]);
        $order_id = $pdo->lastInsertId();

        // 2. Insert order items & update product stock
        $item_stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
        $stock_stmt = $pdo->prepare("UPDATE products SET stock = ? WHERE product_id = ?");

        foreach ($order_items_to_insert as $item) {
            // Insert item
            $item_stmt->execute([
                $order_id,
                $item['product_id'],
                $item['quantity'],
                $item['unit_price'],
                $item['subtotal']
            ]);

            // Update stock
            $stock_stmt->execute([
                $item['new_stock'],
                $item['product_id']
            ]);
        }

        $pdo->commit();

        // Clear cart
        unset($_SESSION['cart']);

        $_SESSION['flash_message'] = [
            'type' => 'success',
            'text' => 'Pesanan Anda berhasil dibuat! Tim kami akan segera memproses pesanan Anda.'
        ];
        header("Location: /jp-annahls/pages/profile.php");
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['flash_message'] = [
            'type' => 'danger',
            'text' => 'Terjadi kesalahan saat memproses pesanan Anda: ' . $e->getMessage()
        ];
        header("Location: /jp-annahls/pages/cart.php");
        exit();
    }
}

// Fetch products currently in the cart
$cart_products = [];
$grand_total = 0;

if (!empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    $in_placeholder = implode(',', array_fill(0, count($product_ids), '?'));
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id IN ($in_placeholder) AND is_active = 1");
        $stmt->execute($product_ids);
        $cart_products = $stmt->fetchAll();
        
        foreach ($cart_products as $key => $prod) {
            $qty = $_SESSION['cart'][$prod['product_id']];
            $cart_products[$key]['qty'] = $qty;
            $cart_products[$key]['subtotal'] = $prod['harga_jual'] * $qty;
            $grand_total += $cart_products[$key]['subtotal'];
        }
    } catch (PDOException $e) {
        $error = 'Gagal memuat produk keranjang: ' . $e->getMessage();
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold" style="color: var(--primary-color);">Keranjang Belanja Anda</h2>
        <p class="text-muted">Periksa kembali daftar jajanan pasar yang ingin Anda pesan.</p>
    </div>
</div>

<div class="row g-4">
    <!-- List of Cart Items -->
    <div class="col-lg-8">
        <?php if (!empty($cart_products)): ?>
            <div class="table-responsive">
                <table class="table table-custom align-middle">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 40%">Produk</th>
                            <th scope="col" class="text-center" style="width: 15%">Harga</th>
                            <th scope="col" class="text-center" style="width: 20%">Jumlah</th>
                            <th scope="col" class="text-center" style="width: 15%">Subtotal</th>
                            <th scope="col" class="text-center" style="width: 10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_products as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div style="width: 60px; height: 60px; background-color: var(--placeholder-bg); border-radius: 10px; overflow: hidden; display:flex; align-items:center; justify-content:center;" class="me-3">
                                            <?php if (!empty($item['image_url'])): ?>
                                                <img src="/jp-annahls/assets/img/<?= esc($item['image_url']) ?>" class="w-100 h-100" style="object-fit: cover;">
                                            <?php else: ?>
                                                <i class="bi bi-image text-muted opacity-50"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-0 text-dark"><?= esc($item['product_name']) ?></h6>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center"><?= format_rupiah($item['harga_jual']) ?></td>
                                <td class="text-center">
                                    <form action="" method="POST" class="d-flex align-items-center justify-content-center gap-1">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                        <input type="number" name="quantity" value="<?= $item['qty'] ?>" min="1" max="<?= $item['stock'] ?>" class="form-control text-center py-1" style="width: 70px;" onchange="this.form.submit()">
                                    </form>
                                </td>
                                <td class="text-center fw-bold" style="color: var(--primary-color);"><?= format_rupiah($item['subtotal']) ?></td>
                                <td class="text-center">
                                    <form action="" method="POST">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm border-0" title="Hapus produk">
                                            <i class="bi bi-trash fs-5"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="card custom-card p-5 text-center border border-1">
                <i class="bi bi-cart-x text-muted opacity-50" style="font-size: 5rem;"></i>
                <h4 class="fw-bold mt-3" style="color: var(--primary-color);">Keranjang Anda Kosong</h4>
                <p class="text-muted">Ayo tambahkan beberapa kue tradisional lezat ke keranjang belanja Anda!</p>
                <a href="/jp-annahls/pages/katalog.php" class="btn btn-primary-pill mt-3 px-4">Lihat Katalog Produk</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Summary Panel -->
    <div class="col-lg-4">
        <div class="card custom-card p-4 border border-1">
            <h5 class="fw-bold mb-4" style="color: var(--primary-color); border-bottom: 2px solid var(--navbar-bg); padding-bottom: 15px;">Ringkasan Belanja</h5>
            <div class="d-flex justify-content-between mb-3">
                <span class="text-muted">Total Item</span>
                <span class="fw-semibold text-dark"><?= count($cart_products) ?> item</span>
            </div>
            <div class="d-flex justify-content-between mb-4">
                <span class="text-muted">Total Pembayaran</span>
                <span class="fw-bold fs-4" style="color: var(--primary-color);"><?= format_rupiah($grand_total) ?></span>
            </div>

            <form action="" method="POST">
                <div class="mb-4">
                    <label for="notes" class="form-label fw-bold">Catatan Pesanan (Opsional)</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Contoh: Kue dibungkus daun pisang, minta sendok dll."></textarea>
                </div>

                <?php if (!empty($cart_products)): ?>
                    <button type="submit" name="checkout" class="btn btn-primary-pill w-100 py-3"><i class="bi bi-credit-card me-2"></i> Proses Checkout</button>
                <?php else: ?>
                    <button type="button" class="btn btn-primary-pill w-100 py-3" disabled><i class="bi bi-credit-card me-2"></i> Proses Checkout</button>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
