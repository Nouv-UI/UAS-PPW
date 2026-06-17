<?php
// pages/admin_dashboard.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Protect page
require_admin();

$error = '';

try {
    // 1. Fetch counts for stats cards
    $total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $total_suppliers = $pdo->query("SELECT COUNT(*) FROM suppliers")->fetchColumn();
    $total_categories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    $total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();

    // 2. Fetch recent 5 orders
    $recent_orders_stmt = $pdo->query("
        SELECT o.*, u.nama_pemesan, hitung_total_order(o.order_id) AS computed_total 
        FROM orders o 
        JOIN users u ON o.user_id = u.user_id 
        ORDER BY o.created_at DESC 
        LIMIT 5
    ");
    $recent_orders = $recent_orders_stmt->fetchAll();

    // 3. Fetch top 3 selling products from view_analitik_produk
    $top_products_stmt = $pdo->query("
        SELECT * FROM view_analitik_produk 
        ORDER BY total_pendapatan DESC 
        LIMIT 3
    ");
    $top_products = $top_products_stmt->fetchAll();

} catch (PDOException $e) {
    $error = 'Gagal memuat data dashboard: ' . $e->getMessage();
    $recent_orders = [];
    $top_products = [];
    $total_products = $total_suppliers = $total_categories = $total_orders = 0;
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold" style="color: var(--primary-color);">Panel Administrasi</h2>
        <p class="text-muted">Selamat datang, <?= esc($_SESSION['nama_pemesan']) ?>. Kelola toko dan pantau performa bisnis Jajan Pasar Anda.</p>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger" style="border-radius: 15px;"><?= esc($error) ?></div>
<?php endif; ?>

<div class="row g-4">
    <!-- Sidebar Navigation -->
    <div class="col-lg-3">
        <div class="admin-sidebar mb-4">
            <h5 class="fw-bold mb-3 px-3" style="color: var(--primary-color);">Menu Admin</h5>
            <div class="list-group list-group-flush">
                <a href="/jp-annahls/pages/admin_dashboard.php" class="list-group-item list-group-item-action active"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                <a href="/jp-annahls/pages/admin_products.php" class="list-group-item list-group-item-action"><i class="bi bi-box-seam me-2"></i> Manajemen Produk</a>
                <a href="/jp-annahls/pages/admin_categories.php" class="list-group-item list-group-item-action"><i class="bi bi-tags me-2"></i> Manajemen Kategori</a>
                <a href="/jp-annahls/pages/admin_suppliers.php" class="list-group-item list-group-item-action"><i class="bi bi-truck me-2"></i> Manajemen Pemasok</a>
                <a href="/jp-annahls/pages/admin_highlights.php" class="list-group-item list-group-item-action"><i class="bi bi-stars me-2"></i> Manajemen Highlight</a>
                <a href="/jp-annahls/pages/admin_orders.php" class="list-group-item list-group-item-action"><i class="bi bi-receipt me-2"></i> Manajemen Pesanan</a>
                <a href="/jp-annahls/pages/admin_analytics.php" class="list-group-item list-group-item-action"><i class="bi bi-graph-up-arrow me-2"></i> Analitik Produk</a>
            </div>
        </div>
    </div>

    <!-- Main Dashboard Contents -->
    <div class="col-lg-9">
        
        <!-- Stats Cards Grid -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card custom-card p-3 border border-1 h-100 d-flex flex-row align-items-center">
                    <div class="p-3 rounded-4 me-3" style="background-color: var(--navbar-bg); color: var(--primary-color);">
                        <i class="bi bi-box-seam fs-3"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-dark"><?= esc($total_products) ?></h4>
                        <span class="text-muted small">Total Produk</span>
                    </div>
                </div>
            </div>
            
            <div class="col-sm-6 col-xl-3">
                <div class="card custom-card p-3 border border-1 h-100 d-flex flex-row align-items-center">
                    <div class="p-3 rounded-4 me-3" style="background-color: #E2F0D9; color: #385723;">
                        <i class="bi bi-truck fs-3"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-dark"><?= esc($total_suppliers) ?></h4>
                        <span class="text-muted small">Total Pemasok</span>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card custom-card p-3 border border-1 h-100 d-flex flex-row align-items-center">
                    <div class="p-3 rounded-4 me-3" style="background-color: #FFF2CC; color: #7F6000;">
                        <i class="bi bi-tags fs-3"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-dark"><?= esc($total_categories) ?></h4>
                        <span class="text-muted small">Total Kategori</span>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card custom-card p-3 border border-1 h-100 d-flex flex-row align-items-center">
                    <div class="p-3 rounded-4 me-3" style="background-color: #FCE4D6; color: #C65911;">
                        <i class="bi bi-receipt fs-3"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0 text-dark"><?= esc($total_orders) ?></h4>
                        <span class="text-muted small">Total Pesanan</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders Table & Top Products -->
        <div class="row g-4 mb-4">
            
            <!-- Recent Orders -->
            <div class="col-xl-8">
                <div class="card custom-card p-4 border border-1 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold m-0" style="color: var(--primary-color);">Pesanan Terbaru</h5>
                        <a href="/jp-annahls/pages/admin_orders.php" class="btn btn-tertiary-rounded py-1 px-3" style="font-size: 0.85rem;">Semua Pesanan</a>
                    </div>
                    <?php if (!empty($recent_orders)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Pemesan</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_orders as $ord): ?>
                                        <tr>
                                            <td><strong>#<?= esc($ord['order_id']) ?></strong></td>
                                            <td><?= esc($ord['nama_pemesan']) ?></td>
                                            <td class="fw-bold"><?= format_rupiah($ord['computed_total']) ?></td>
                                            <td>
                                                <?php if ($ord['status'] === 'pending'): ?>
                                                    <span class="badge bg-warning text-dark rounded-pill">Pending</span>
                                                <?php elseif ($ord['status'] === 'confirmed'): ?>
                                                    <span class="badge bg-success text-white rounded-pill">Confirmed</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger text-white rounded-pill">Cancelled</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted small text-center my-4">Belum ada pesanan masuk.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Top Products -->
            <div class="col-xl-4">
                <div class="card custom-card p-4 border border-1 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold m-0" style="color: var(--primary-color);">Produk Terlaris</h5>
                        <a href="/jp-annahls/pages/admin_analytics.php" class="btn btn-tertiary-rounded py-1 px-3" style="font-size: 0.85rem;">Analitik</a>
                    </div>
                    <?php if (!empty($top_products)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($top_products as $key => $tp): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div class="d-flex align-items-center">
                                        <span class="badge rounded-circle badge-primary me-2 d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;"><?= $key + 1 ?></span>
                                        <span class="fw-semibold text-dark" style="font-size: 0.9rem;"><?= esc($tp['product_name']) ?></span>
                                    </div>
                                    <span class="small text-muted fw-bold"><?= esc($tp['total_item_terjual']) ?> unit</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted small text-center my-4">Belum ada data penjualan.</p>
                    <?php endif; ?>
                </div>
            </div>

        </div>

    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
