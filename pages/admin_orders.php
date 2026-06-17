<?php
// pages/admin_orders.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Protect page
require_admin();

$error = '';
$success = '';

// Handle Status Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
    $status = trim($_POST['status'] ?? '');

    if ($order_id > 0 && !empty($status)) {
        try {
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
            $stmt->execute([$status, $order_id]);
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'text' => 'Status pesanan #' . $order_id . ' berhasil diperbarui menjadi ' . $status . '.'
            ];
        } catch (PDOException $e) {
            // Check for DB trigger error (SQLSTATE 45000, Custom SIGNAL message)
            if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1644) {
                $_SESSION['flash_message'] = [
                    'type' => 'danger',
                    'text' => 'Gagal memperbarui status: ' . $e->errorInfo[2]
                ];
            } else {
                $_SESSION['flash_message'] = [
                    'type' => 'danger',
                    'text' => 'Gagal memperbarui status: ' . $e->getMessage()
                ];
            }
        }
    }
    header("Location: /jp-annahls/pages/admin_orders.php");
    exit();
}

// Fetch all orders using hitung_total_order() function
$orders = [];
try {
    $stmt = $pdo->query("
        SELECT o.*, u.nama_pemesan, u.phone_number, hitung_total_order(o.order_id) AS computed_total 
        FROM orders o 
        JOIN users u ON o.user_id = u.user_id 
        ORDER BY o.created_at DESC
    ");
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Gagal memuat daftar pesanan: ' . $e->getMessage();
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold" style="color: var(--primary-color);">Manajemen Pesanan</h2>
        <p class="text-muted">Pantau daftar pesanan masuk dari pembeli, verifikasi detail item, dan perbarui status pengerjaan.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Sidebar Navigation -->
    <div class="col-lg-3">
        <div class="admin-sidebar mb-4">
            <h5 class="fw-bold mb-3 px-3" style="color: var(--primary-color);">Menu Admin</h5>
            <div class="list-group list-group-flush">
                <a href="/jp-annahls/pages/admin_dashboard.php" class="list-group-item list-group-item-action<?= basename($_SERVER['SCRIPT_NAME']) === 'admin_dashboard.php' ? ' active' : '' ?> d-flex align-items-center">
                    <span class="material-icons-outlined me-2" style="font-size: 1.25rem;">dashboard</span> Dashboard
                </a>
                <a href="/jp-annahls/pages/admin_products.php" class="list-group-item list-group-item-action<?= basename($_SERVER['SCRIPT_NAME']) === 'admin_products.php' ? ' active' : '' ?> d-flex align-items-center">
                    <span class="material-icons-outlined me-2" style="font-size: 1.25rem;">grid_view</span> Produk
                </a>
                <a href="/jp-annahls/pages/admin_categories.php" class="list-group-item list-group-item-action<?= basename($_SERVER['SCRIPT_NAME']) === 'admin_categories.php' ? ' active' : '' ?> d-flex align-items-center">
                    <span class="material-icons-outlined me-2" style="font-size: 1.25rem;">sell</span> Kategori
                </a>
                <a href="/jp-annahls/pages/admin_suppliers.php" class="list-group-item list-group-item-action<?= basename($_SERVER['SCRIPT_NAME']) === 'admin_suppliers.php' ? ' active' : '' ?> d-flex align-items-center">
                    <span class="material-icons-outlined me-2" style="font-size: 1.25rem;">local_shipping</span> Suplier
                </a>
                <a href="/jp-annahls/pages/admin_highlights.php" class="list-group-item list-group-item-action<?= basename($_SERVER['SCRIPT_NAME']) === 'admin_highlights.php' ? ' active' : '' ?> d-flex align-items-center">
                    <span class="material-icons-outlined me-2" style="font-size: 1.25rem;">auto_awesome</span> Highlight
                </a>
                <a href="/jp-annahls/pages/admin_orders.php" class="list-group-item list-group-item-action<?= basename($_SERVER['SCRIPT_NAME']) === 'admin_orders.php' ? ' active' : '' ?> d-flex align-items-center">
                    <span class="material-icons-outlined me-2" style="font-size: 1.25rem;">receipt_long</span> Pesanan
                </a>
                <a href="/jp-annahls/pages/admin_analytics.php" class="list-group-item list-group-item-action<?= basename($_SERVER['SCRIPT_NAME']) === 'admin_analytics.php' ? ' active' : '' ?> d-flex align-items-center">
                    <span class="material-icons-outlined me-2" style="font-size: 1.25rem;">trending_up</span> Analisis
                </a>
            </div>
        </div>
    </div>

    <!-- Main Workspace -->
    <div class="col-lg-9">
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" style="border-radius: 15px;"><?= esc($error) ?></div>
        <?php endif; ?>

        <div class="card custom-card p-4 border border-1">
            <h5 class="fw-bold mb-4" style="color: var(--primary-color);">Daftar Semua Transaksi Pesanan</h5>

            <div class="table-responsive">
                <table class="table table-custom align-middle">
                    <thead>
                        <tr>
                            <th>ID Order</th>
                            <th>Pelanggan</th>
                            <th>Tanggal Masuk</th>
                            <th>Total Belanja</th>
                            <th>Catatan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($orders)): ?>
                            <?php foreach ($orders as $ord): ?>
                                <tr>
                                    <td><strong>#<?= esc($ord['order_id']) ?></strong></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= esc($ord['nama_pemesan']) ?></div>
                                        <span class="text-muted small"><?= esc($ord['phone_number'] ?: '-') ?></span>
                                    </td>
                                    <td class="small"><?= esc(date('d M Y, H:i', strtotime($ord['created_at']))) ?></td>
                                    <td class="fw-bold text-dark"><?= format_rupiah($ord['computed_total']) ?></td>
                                    <td class="small text-muted" style="max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <?= esc($ord['notes'] ?: '-') ?>
                                    </td>
                                    <td>
                                        <?php if ($ord['status'] === 'pending'): ?>
                                            <span class="badge bg-warning text-dark rounded-pill">Pending</span>
                                        <?php elseif ($ord['status'] === 'confirmed'): ?>
                                            <span class="badge bg-success text-white rounded-pill">Confirmed</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger text-white rounded-pill">Cancelled</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <!-- Button Trigger Detail Modal -->
                                            <button class="btn btn-sm btn-outline-info d-flex align-items-center justify-content-center" data-bs-toggle="modal" data-bs-target="#orderDetailModalAdmin<?= $ord['order_id'] ?>" title="Detail Item" style="width: 32px; height: 32px; padding: 0;"><span class="material-icons-outlined" style="font-size: 1.15rem;">visibility</span></button>
                                            
                                            <!-- Button Trigger Edit Status Modal -->
                                            <button class="btn btn-sm btn-outline-warning d-flex align-items-center justify-content-center" data-bs-toggle="modal" data-bs-target="#orderStatusModalAdmin<?= $ord['order_id'] ?>" title="Update Status" style="width: 32px; height: 32px; padding: 0;"><span class="material-icons-outlined" style="font-size: 1.15rem;">settings</span></button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- MODAL DETIL PESANAN -->
                                <div class="modal fade" id="orderDetailModalAdmin<?= $ord['order_id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content" style="border-radius: 20px;">
                                            <div class="modal-header border-0 pb-0">
                                                <h5 class="modal-title fw-bold" style="color: var(--primary-color);">Daftar Item Order #<?= esc($ord['order_id']) ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p class="text-muted small mb-3">Pemesan: <strong><?= esc($ord['nama_pemesan']) ?></strong> (<?= esc($ord['phone_number'] ?: '-') ?>)</p>
                                                
                                                <?php
                                                try {
                                                    $item_stmt = $pdo->prepare("
                                                        SELECT oi.*, p.product_name 
                                                        FROM order_items oi
                                                        JOIN products p ON oi.product_id = p.product_id
                                                        WHERE oi.order_id = ?
                                                    ");
                                                    $item_stmt->execute([$ord['order_id']]);
                                                    $items = $item_stmt->fetchAll();
                                                } catch (PDOException $e) {
                                                    $items = [];
                                                }
                                                ?>
                                                
                                                <ul class="list-group list-group-flush mb-4">
                                                    <?php foreach ($items as $it): ?>
                                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                            <div>
                                                                <h6 class="mb-0 fw-semibold text-dark"><?= esc($it['product_name']) ?></h6>
                                                                <small class="text-muted"><?= esc($it['quantity']) ?> x <?= format_rupiah($it['unit_price']) ?></small>
                                                            </div>
                                                            <span class="fw-bold text-dark"><?= format_rupiah($it['subtotal']) ?></span>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>

                                                <?php if (!empty($ord['notes'])): ?>
                                                    <div class="bg-light p-3 rounded-3 mb-3">
                                                        <h6 class="fw-bold mb-1" style="font-size: 0.9rem;">Catatan Pembeli:</h6>
                                                        <p class="mb-0 small text-muted"><?= esc($ord['notes']) ?></p>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="d-flex justify-content-between align-items-center pt-2" style="border-top: 1px solid #ECECEC;">
                                                    <span class="fw-semibold text-muted">Total Pembayaran</span>
                                                    <span class="fw-bold fs-5" style="color: var(--primary-color);"><?= format_rupiah($ord['computed_total']) ?></span>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0">
                                                <button type="button" class="btn btn-secondary-pill px-4" data-bs-dismiss="modal">Tutup</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- MODAL UPDATE STATUS -->
                                <div class="modal fade" id="orderStatusModalAdmin<?= $ord['order_id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-sm">
                                        <div class="modal-content" style="border-radius: 20px;">
                                            <div class="modal-header border-0 pb-0">
                                                <h5 class="modal-title fw-bold" style="color: var(--primary-color);">Update Status</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="" method="POST" class="confirm-save-edit">
                                                <input type="hidden" name="update_status" value="1">
                                                <input type="hidden" name="order_id" value="<?= $ord['order_id'] ?>">
                                                
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label text-muted small">Status Saat Ini: <strong><?= strtoupper($ord['status']) ?></strong></label>
                                                        <select class="form-select" name="status" required>
                                                            <option value="pending" <?= $ord['status'] === 'pending' ? 'selected' : '' ?>>PENDING</option>
                                                            <option value="confirmed" <?= $ord['status'] === 'confirmed' ? 'selected' : '' ?>>CONFIRMED</option>
                                                            <option value="cancelled" <?= $ord['status'] === 'cancelled' ? 'selected' : '' ?>>CANCELLED</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0">
                                                    <button type="button" class="btn btn-secondary-pill px-3 py-1 btn-sm" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary-pill px-3 py-1 btn-sm">Perbarui</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Belum ada pesanan masuk.</td>
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
