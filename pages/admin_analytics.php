<?php
// pages/admin_analytics.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Protect page
require_admin();

$error = '';
$analytics = [];
$total_revenue = 0;
$total_profit = 0;
$total_items_sold = 0;

try {
    // Fetch product performance details, including calling hitung_margin_produk() function
    $stmt = $pdo->query("
        SELECT va.*, 
               p.harga_supplier, 
               p.harga_jual, 
               hitung_margin_produk(va.product_id) AS margin_per_item,
               (hitung_margin_produk(va.product_id) * va.total_item_terjual) AS total_keuntungan
        FROM view_analitik_produk va
        JOIN products p ON va.product_id = p.product_id
        ORDER BY va.total_pendapatan DESC
    ");
    $analytics = $stmt->fetchAll();

    // Calculate overall summaries
    foreach ($analytics as $row) {
        $total_revenue += (float)$row['total_pendapatan'];
        $total_profit += (float)$row['total_keuntungan'];
        $total_items_sold += (int)$row['total_item_terjual'];
    }

} catch (PDOException $e) {
    $error = 'Gagal memuat analitik penjualan: ' . $e->getMessage();
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold" style="color: var(--primary-color);">Analitik Penjualan</h2>
        <p class="text-muted">Analisis keuntungan produk, volume penjualan, dan profit margin secara real-time.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Sidebar Navigation -->
    <div class="col-lg-3">
        <div class="admin-sidebar mb-4">
            <h5 class="fw-bold mb-3 px-3" style="color: var(--primary-color);">Menu Admin</h5>
            <div class="list-group list-group-flush">
                <a href="/jp-annahls/pages/admin_dashboard.php" class="list-group-item list-group-item-action"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                <a href="/jp-annahls/pages/admin_products.php" class="list-group-item list-group-item-action"><i class="bi bi-box-seam me-2"></i> Manajemen Produk</a>
                <a href="/jp-annahls/pages/admin_categories.php" class="list-group-item list-group-item-action"><i class="bi bi-tags me-2"></i> Manajemen Kategori</a>
                <a href="/jp-annahls/pages/admin_suppliers.php" class="list-group-item list-group-item-action"><i class="bi bi-truck me-2"></i> Manajemen Pemasok</a>
                <a href="/jp-annahls/pages/admin_highlights.php" class="list-group-item list-group-item-action"><i class="bi bi-stars me-2"></i> Manajemen Highlight</a>
                <a href="/jp-annahls/pages/admin_orders.php" class="list-group-item list-group-item-action"><i class="bi bi-receipt me-2"></i> Manajemen Pesanan</a>
                <a href="/jp-annahls/pages/admin_analytics.php" class="list-group-item list-group-item-action active"><i class="bi bi-graph-up-arrow me-2"></i> Analitik Produk</a>
            </div>
        </div>
    </div>

    <!-- Main Workspace -->
    <div class="col-lg-9">
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" style="border-radius: 15px;"><?= esc($error) ?></div>
        <?php endif; ?>

        <!-- Overall Summary Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card custom-card p-3 border border-1 text-center bg-white">
                    <span class="text-muted small text-uppercase fw-semibold">Total Pendapatan Kotor</span>
                    <h3 class="fw-bold mt-2 mb-0" style="color: var(--primary-color);"><?= format_rupiah($total_revenue) ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card custom-card p-3 border border-1 text-center bg-white">
                    <span class="text-muted small text-uppercase fw-semibold">Total Estimasi Laba Bersih</span>
                    <h3 class="fw-bold mt-2 mb-0 text-success"><?= format_rupiah($total_profit) ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card custom-card p-3 border border-1 text-center bg-white">
                    <span class="text-muted small text-uppercase fw-semibold">Total Item Terjual</span>
                    <h3 class="fw-bold mt-2 mb-0 text-dark"><?= esc($total_items_sold) ?> unit</h3>
                </div>
            </div>
        </div>

        <!-- Analytics Table -->
        <div class="card custom-card p-4 border border-1">
            <h5 class="fw-bold mb-4" style="color: var(--primary-color);">Laporan Kinerja Tiap Produk</h5>

            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0" style="font-size: 0.95rem;">
                    <thead>
                        <tr>
                            <th>Nama Produk</th>
                            <th class="text-center">Frekuensi Order</th>
                            <th class="text-center">Item Terjual</th>
                            <th class="text-end">Pendapatan Kotor</th>
                            <th class="text-end">Margin/Item</th>
                            <th class="text-end">Total Laba Bersih</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($analytics)): ?>
                            <?php foreach ($analytics as $row): ?>
                                <tr>
                                    <td><strong class="text-dark"><?= esc($row['product_name']) ?></strong></td>
                                    <td class="text-center"><?= esc($row['total_frekuensi_pesanan']) ?> kali</td>
                                    <td class="text-center fw-semibold"><?= esc($row['total_item_terjual']) ?> unit</td>
                                    <td class="text-end text-muted"><?= format_rupiah($row['total_pendapatan']) ?></td>
                                    <td class="text-end text-secondary small">
                                        <?= format_rupiah($row['margin_per_item']) ?> 
                                        <div style="font-size: 0.75rem;" class="text-muted">
                                            (<?= round(($row['margin_per_item'] / ($row['harga_jual'] ?: 1)) * 100) ?>% Harga)
                                        </div>
                                    </td>
                                    <td class="text-end fw-bold text-success"><?= format_rupiah($row['total_keuntungan']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Belum ada transaksi penjualan yang terkonfirmasi.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
