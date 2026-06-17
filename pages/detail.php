<?php
// pages/detail.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/header.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    // Fetch product details, supplier info, and categories in one join query
    $stmt = $pdo->prepare("
        SELECT p.*, s.supplier_name, s.phone_number AS supplier_phone, s.city AS supplier_city,
               GROUP_CONCAT(c.category_name SEPARATOR ', ') AS categories_list
        FROM products p
        LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
        LEFT JOIN product_categories pc ON p.product_id = pc.product_id
        LEFT JOIN categories c ON pc.category_id = c.category_id
        WHERE p.product_id = ? AND p.is_active = 1
        GROUP BY p.product_id
    ");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
} catch (PDOException $e) {
    $product = null;
    $error = 'Gagal memuat detail produk: ' . $e->getMessage();
}

if (!$product) {
    echo '<div class="alert alert-warning py-5 text-center my-5" style="border-radius:20px;">';
    echo '  <i class="bi bi-exclamation-circle fs-1 text-warning"></i>';
    echo '  <h3 class="fw-bold mt-3">Produk Tidak Ditemukan</h3>';
    echo '  <p class="text-muted">Produk yang Anda cari tidak terdaftar atau sudah tidak aktif.</p>';
    echo '  <a href="/jp-annahls/pages/katalog.php" class="btn btn-primary-pill mt-3 px-4">Kembali ke Katalog</a>';
    echo '</div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit();
}

// Handle Add to Cart or Buy Now form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $qty = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    if ($qty < 1) $qty = 1;

    // Check stock availability
    if ($qty > $product['stock']) {
        $_SESSION['flash_message'] = [
            'type' => 'danger',
            'text' => 'Stok produk tidak mencukupi. Stok saat ini: ' . $product['stock'] . ' unit.'
        ];
        header("Location: /jp-annahls/pages/detail.php?id=" . $product_id);
        exit();
    }

    // Initialize cart in session if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Add item or increment quantity
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $qty;
    } else {
        $_SESSION['cart'][$product_id] = $qty;
    }

    // Ensure we don't exceed stock
    if ($_SESSION['cart'][$product_id] > $product['stock']) {
        $_SESSION['cart'][$product_id] = $product['stock'];
    }

    $_SESSION['flash_message'] = [
        'type' => 'success',
        'text' => 'Produk berhasil ditambahkan ke keranjang belanja.'
    ];

    if (isset($_POST['buy_now'])) {
        header("Location: /jp-annahls/pages/cart.php");
    } else {
        header("Location: /jp-annahls/pages/detail.php?id=" . $product_id);
    }
    exit();
}
?>

<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/jp-annahls/index.php" style="color: var(--primary-color);">Beranda</a></li>
                <li class="breadcrumb-item"><a href="/jp-annahls/pages/katalog.php" style="color: var(--primary-color);">Katalog</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= esc($product['product_name']) ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="row g-5">
    <!-- Product Image -->
    <div class="col-md-6">
        <div class="card custom-card p-2 border border-1 d-flex align-items-center justify-content-center" style="background-color: var(--placeholder-bg); height: 400px;">
            <?php if (!empty($product['image_url'])): ?>
                <img src="/jp-annahls/assets/img/<?= esc($product['image_url']) ?>" class="w-100 h-100 rounded-4" alt="<?= esc($product['product_name']) ?>" style="object-fit: cover;">
            <?php else: ?>
                <div class="text-center text-muted">
                    <i class="bi bi-image fs-1 opacity-50" style="font-size: 5rem !important;"></i>
                    <h4 class="mt-3 opacity-75"><?= esc($product['product_name']) ?></h4>
                    <span class="text-muted">Tidak Ada Foto Produk</span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Product Details and Actions -->
    <div class="col-md-6 d-flex flex-column justify-content-between">
        <div>
            <!-- Category Badges -->
            <div class="mb-3">
                <?php 
                $p_cats = explode(', ', $product['categories_list'] ?? '');
                foreach ($p_cats as $cat_name):
                    if (empty($cat_name)) continue;
                ?>
                    <span class="badge badge-primary rounded-pill px-3 py-2 me-1" style="font-size: 0.85rem;">
                        <?= esc($cat_name) ?>
                    </span>
                <?php endforeach; ?>
            </div>

            <h2 class="fw-bold mb-2" style="color: var(--primary-color);"><?= esc($product['product_name']) ?></h2>
            <div class="fs-3 fw-bold text-dark mb-4"><?= format_rupiah($product['harga_jual']) ?></div>
            
            <p class="text-muted mb-4" style="line-height: 1.7; font-size: 1rem;">
                <?= esc($product['description'] ?: 'Tidak ada deskripsi untuk produk ini.') ?>
            </p>

            <hr class="mb-4" style="border-color: #E5E7EB;">


            <div class="d-flex align-items-center mb-4">
                <span class="text-muted me-3"><i class="bi bi-box-seam"></i> Stok Tersedia:</span>
                <span class="fw-bold text-dark"><?= esc($product['stock']) ?> unit</span>
            </div>
        </div>

        <!-- Order Action Forms -->
        <div>
            <?php if ($product['stock'] > 0): ?>
                <form action="" method="POST" class="needs-validation" novalidate>
                    <div class="row g-3 align-items-center mb-4">
                        <div class="col-auto">
                            <label for="quantity" class="form-label mb-0 fw-bold">Jumlah Beli</label>
                        </div>
                        <div class="col-4 col-sm-3">
                            <input type="number" class="form-control text-center" id="quantity" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>" required>
                        </div>
                        <div class="col-auto">
                            <span class="text-muted small">Max: <?= esc($product['stock']) ?> unit</span>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <button type="submit" name="add_to_cart" class="btn btn-secondary-pill w-100 py-3"><i class="bi bi-cart-plus me-2"></i> Keranjang</button>
                        </div>
                        <div class="col-sm-6">
                            <button type="submit" name="buy_now" class="btn btn-primary-pill w-100 py-3"><i class="bi bi-lightning-fill me-2"></i> Beli Sekarang</button>
                        </div>
                    </div>
                </form>
            <?php else: ?>
                <div class="alert alert-danger" style="border-radius: 15px;">
                    <i class="bi bi-exclamation-octagon-fill me-2"></i> Stok Habis! Maaf, produk ini sedang tidak tersedia untuk sementara waktu.
                </div>
                <a href="/jp-annahls/pages/katalog.php" class="btn btn-secondary-pill w-100 py-3"><i class="bi bi-arrow-left"></i> Kembali ke Katalog</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
