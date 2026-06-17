<?php
// pages/profile.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Require login
require_login();

$user = get_logged_in_user();
$error = '';
$success = '';

// Handle Profile Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $nama_pemesan = trim($_POST['nama_pemesan'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');

    if (empty($nama_pemesan) || empty($phone_number)) {
        $error = 'Nama Lengkap dan Nomor Telepon wajib diisi.';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE users SET nama_pemesan = ?, phone_number = ? WHERE user_id = ?");
            $stmt->execute([$nama_pemesan, $phone_number, $user['user_id']]);
            $success = 'Profil Anda berhasil diperbarui!';
            $_SESSION['nama_pemesan'] = $nama_pemesan; // Update session
            
            // Reload user data
            $user = get_logged_in_user();
        } catch (PDOException $e) {
            $error = 'Gagal memperbarui profil: ' . $e->getMessage();
        }
    }
}

// Fetch order history using hitung_total_order() function
$orders = [];
try {
    // Explicitly use MySQL hitung_total_order function
    $stmt = $pdo->prepare("
        SELECT o.*, hitung_total_order(o.order_id) AS computed_total 
        FROM orders o 
        WHERE o.user_id = ? 
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$user['user_id']]);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Gagal memuat riwayat pesanan: ' . $e->getMessage();
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold" style="color: var(--primary-color);">Profil Pengguna</h2>
        <p class="text-muted">Kelola data diri Anda dan tinjau riwayat pemesanan jajanan pasar Anda.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Edit Profile Form -->
    <div class="col-lg-4">
        <div class="card custom-card p-4 border border-1">
            <h5 class="fw-bold mb-4" style="color: var(--primary-color); border-bottom: 2px solid var(--navbar-bg); padding-bottom: 15px;">Informasi Pribadi</h5>
            
            <?php if (!empty($error) && isset($_POST['update_profile'])): ?>
                <div class="alert alert-danger" style="border-radius: 10px;"><?= esc($error) ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success" style="border-radius: 10px;"><?= esc($success) ?></div>
            <?php endif; ?>

            <form action="" method="POST" class="needs-validation confirm-save-edit" novalidate>
                <input type="hidden" name="update_profile" value="1">
                
                <div class="mb-3">
                    <label class="form-label text-muted small mb-1">Username (Tidak dapat diubah)</label>
                    <input type="text" class="form-control bg-light" value="<?= esc($user['username']) ?>" readonly>
                </div>

                <div class="mb-3">
                    <label for="nama_pemesan" class="form-label fw-bold">Nama Lengkap</label>
                    <input type="text" class="form-control" id="nama_pemesan" name="nama_pemesan" value="<?= esc($user['nama_pemesan']) ?>" required>
                    <div class="invalid-feedback">Nama Lengkap wajib diisi.</div>
                </div>

                <div class="mb-4">
                    <label for="phone_number" class="form-label fw-bold">Nomor Telepon/WA</label>
                    <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?= esc($user['phone_number']) ?>" required>
                    <div class="invalid-feedback">Nomor Telepon wajib diisi.</div>
                </div>

                <button type="submit" class="btn btn-primary-pill w-100 py-2"><i class="bi bi-save me-2"></i> Simpan Perubahan</button>
            </form>
        </div>
    </div>

    <!-- Order History List -->
    <div class="col-lg-8">
        <div class="card custom-card p-4 border border-1">
            <h5 class="fw-bold mb-4" style="color: var(--primary-color); border-bottom: 2px solid var(--navbar-bg); padding-bottom: 15px;">Riwayat Pesanan</h5>
            
            <?php if (!empty($orders)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle" style="font-size: 0.95rem;">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Tanggal</th>
                                <th>Total Bayar</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $ord): ?>
                                <tr>
                                    <td><strong>#<?= esc($ord['order_id']) ?></strong></td>
                                    <td><?= esc(date('d M Y, H:i', strtotime($ord['created_at']))) ?> WIB</td>
                                    <td class="fw-bold" style="color: var(--primary-color);"><?= format_rupiah($ord['computed_total']) ?></td>
                                    <td>
                                        <?php if ($ord['status'] === 'pending'): ?>
                                            <span class="badge bg-warning text-dark border border-warning rounded-pill px-3 py-1">Menunggu Konfirmasi</span>
                                        <?php elseif ($ord['status'] === 'confirmed'): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">Dikonfirmasi</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1">Dibatalkan</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-tertiary-rounded py-1 px-3" data-bs-toggle="modal" data-bs-target="#orderModal<?= $ord['order_id'] ?>">
                                            Detail
                                        </button>
                                    </td>
                                </tr>

                                <!-- Modal Details for each Order -->
                                <div class="modal fade" id="orderModal<?= $ord['order_id'] ?>" tabindex="-1" aria-labelledby="orderModalLabel<?= $ord['order_id'] ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content" style="border-radius: 20px;">
                                            <div class="modal-header border-0 pb-0">
                                                <h5 class="modal-title fw-bold" id="orderModalLabel<?= $ord['order_id'] ?>" style="color: var(--primary-color);">Detail Order #<?= esc($ord['order_id']) ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p class="text-muted small mb-3">Dipesan pada: <?= esc(date('d M Y, H:i', strtotime($ord['created_at']))) ?> WIB</p>
                                                
                                                <!-- Fetch Order Items -->
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
                                                        <h6 class="fw-bold mb-1" style="font-size: 0.9rem;"><i class="bi bi-journal-text me-1"></i> Catatan Anda:</h6>
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
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-receipt text-muted opacity-50" style="font-size: 4rem;"></i>
                    <p class="text-muted mt-3 mb-0">Anda belum pernah melakukan pemesanan.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
