<?php
// pages/admin_suppliers.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Protect page
require_admin();

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$error = '';
$success = '';

// Handle Delete Operation
if ($action === 'delete') {
    $supplier_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    try {
        // Soft-delete or hard-delete? Since XAMPP DB setup has is_active flag in suppliers, let's toggle `is_active = 0`!
        // This is much safer and respects foreign key constraints!
        $stmt = $pdo->prepare("UPDATE suppliers SET is_active = 0 WHERE supplier_id = ?");
        $stmt->execute([$supplier_id]);
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'text' => 'Pemasok berhasil dinonaktifkan!'
        ];
    } catch (PDOException $e) {
        $_SESSION['flash_message'] = [
            'type' => 'danger',
            'text' => 'Gagal menonaktifkan pemasok: ' . $e->getMessage()
        ];
    }
    header("Location: /jp-annahls/pages/admin_suppliers.php");
    exit();
}

// Handle Add/Edit Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supplier_id = isset($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : 0;
    $supplier_name = trim($_POST['supplier_name'] ?? '');
    $contact_person = trim($_POST['contact_person'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $province = trim($_POST['province'] ?? '');

    if (empty($supplier_name)) {
        $error = 'Nama pemasok wajib diisi.';
    } else {
        try {
            if ($supplier_id === 0) {
                // ADD NEW
                $stmt = $pdo->prepare("
                    INSERT INTO suppliers (supplier_name, contact_person, phone_number, address, city, province, is_active) 
                    VALUES (?, ?, ?, ?, ?, ?, 1)
                ");
                $stmt->execute([$supplier_name, $contact_person, $phone_number, $address, $city, $province]);
            } else {
                // EDIT EXISTING
                $stmt = $pdo->prepare("
                    UPDATE suppliers 
                    SET supplier_name = ?, contact_person = ?, phone_number = ?, address = ?, city = ?, province = ? 
                    WHERE supplier_id = ?
                ");
                $stmt->execute([$supplier_name, $contact_person, $phone_number, $address, $city, $province, $supplier_id]);
            }

            $_SESSION['flash_message'] = [
                'type' => 'success',
                'text' => 'Data pemasok berhasil disimpan!'
            ];
            header("Location: /jp-annahls/pages/admin_suppliers.php");
            exit();

        } catch (PDOException $e) {
            $error = 'Gagal menyimpan pemasok: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold" style="color: var(--primary-color);">Manajemen Pemasok (Supplier)</h2>
        <p class="text-muted">Kelola kemitraan pemasok bahan baku maupun jajanan pasar titipan di toko Anda.</p>
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
                <a href="/jp-annahls/pages/admin_suppliers.php" class="list-group-item list-group-item-action active"><i class="bi bi-truck me-2"></i> Manajemen Pemasok</a>
                <a href="/jp-annahls/pages/admin_highlights.php" class="list-group-item list-group-item-action"><i class="bi bi-stars me-2"></i> Manajemen Highlight</a>
                <a href="/jp-annahls/pages/admin_orders.php" class="list-group-item list-group-item-action"><i class="bi bi-receipt me-2"></i> Manajemen Pesanan</a>
                <a href="/jp-annahls/pages/admin_analytics.php" class="list-group-item list-group-item-action"><i class="bi bi-graph-up-arrow me-2"></i> Analitik Produk</a>
            </div>
        </div>
    </div>

    <!-- Main Workspace -->
    <div class="col-lg-9">
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 15px;">
                <i class="bi bi-exclamation-octagon-fill me-2"></i> <?= esc($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($action === 'list'): ?>
            <!-- LIST SUPPLIERS VIEW -->
            <?php
            try {
                // Fetch active suppliers
                $suppliers = $pdo->query("SELECT * FROM suppliers WHERE is_active = 1 ORDER BY supplier_id DESC")->fetchAll();
            } catch (PDOException $e) {
                $suppliers = [];
                $error = 'Gagal memuat daftar pemasok: ' . $e->getMessage();
            }
            ?>

            <div class="card custom-card p-4 border border-1">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold m-0" style="color: var(--primary-color);">Daftar Pemasok Aktif</h5>
                    <a href="/jp-annahls/pages/admin_suppliers.php?action=add" class="btn btn-primary-pill"><i class="bi bi-plus-circle me-1"></i> Tambah Pemasok Baru</a>
                </div>

                <div class="table-responsive">
                    <table class="table table-custom align-middle">
                        <thead>
                            <tr>
                                <th>Pemasok</th>
                                <th>Narahubung</th>
                                <th>Telepon</th>
                                <th>Kota/Provinsi</th>
                                <th>Alamat Lengkap</th>
                                <th class="text-center" style="width: 10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($suppliers)): ?>
                                <?php foreach ($suppliers as $s): ?>
                                    <tr>
                                        <td><strong class="text-dark"><?= esc($s['supplier_name']) ?></strong></td>
                                        <td><?= esc($s['contact_person'] ?: '-') ?></td>
                                        <td><?= esc($s['phone_number'] ?: '-') ?></td>
                                        <td><?= esc($s['city']) ?>, <?= esc($s['province']) ?></td>
                                        <td class="small text-muted"><?= esc($s['address'] ?: '-') ?></td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="/jp-annahls/pages/admin_suppliers.php?action=edit&id=<?= $s['supplier_id'] ?>" class="btn btn-sm btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                                                <a href="/jp-annahls/pages/admin_suppliers.php?action=delete&id=<?= $s['supplier_id'] ?>" class="btn btn-sm btn-outline-danger confirm-delete" data-item-name="<?= esc($s['supplier_name']) ?>" title="Hapus"><i class="bi bi-trash"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">Belum ada pemasok yang terdaftar.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($action === 'add' || $action === 'edit'): ?>
            <!-- ADD / EDIT FORM VIEW -->
            <?php
            $supplier = [
                'supplier_id' => 0,
                'supplier_name' => '',
                'contact_person' => '',
                'phone_number' => '',
                'address' => '',
                'city' => '',
                'province' => ''
            ];

            if ($action === 'edit') {
                $supplier_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
                try {
                    $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE supplier_id = ?");
                    $stmt->execute([$supplier_id]);
                    $fetched_supplier = $stmt->fetch();
                    if ($fetched_supplier) {
                        $supplier = $fetched_supplier;
                    } else {
                        $error = 'Pemasok tidak ditemukan.';
                        $action = 'add';
                    }
                } catch (PDOException $e) {
                    $error = 'Gagal memuat data pemasok: ' . $e->getMessage();
                }
            }
            ?>

            <div class="card custom-card p-4 border border-1">
                <h5 class="fw-bold mb-4 text-uppercase" style="color: var(--primary-color);">
                    <?= $action === 'add' ? 'Tambah Pemasok Baru' : 'Edit Pemasok #' . $supplier['supplier_id'] ?>
                </h5>

                <form action="" method="POST" class="needs-validation <?= $action === 'edit' ? 'confirm-save-edit' : '' ?>" novalidate>
                    <input type="hidden" name="supplier_id" value="<?= esc($supplier['supplier_id']) ?>">

                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label for="supplier_name" class="form-label fw-bold">Nama Pemasok (Badan Usaha/Perorangan) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="supplier_name" name="supplier_name" value="<?= esc($supplier['supplier_name']) ?>" required>
                            <div class="invalid-feedback">Nama pemasok wajib diisi.</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="contact_person" class="form-label fw-bold">Nama Narahubung</label>
                            <input type="text" class="form-control" id="contact_person" name="contact_person" value="<?= esc($supplier['contact_person']) ?>">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="phone_number" class="form-label fw-bold">Nomor Telepon/WA</label>
                            <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?= esc($supplier['phone_number']) ?>">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="city" class="form-label fw-bold">Kota <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="city" name="city" value="<?= esc($supplier['city']) ?>" required>
                            <div class="invalid-feedback">Kota wajib diisi.</div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="province" class="form-label fw-bold">Provinsi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="province" name="province" value="<?= esc($supplier['province']) ?>" required>
                            <div class="invalid-feedback">Provinsi wajib diisi.</div>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="address" class="form-label fw-bold">Alamat Lengkap</label>
                            <textarea class="form-control" id="address" name="address" rows="3"><?= esc($supplier['address']) ?></textarea>
                        </div>
                    </div>

                    <div class="d-flex gap-3 justify-content-end mt-4" style="border-top:1px solid #ECECEC; padding-top: 20px;">
                        <a href="/jp-annahls/pages/admin_suppliers.php" class="btn btn-secondary-pill px-4">Batal</a>
                        <button type="submit" class="btn btn-primary-pill px-5">Simpan Pemasok</button>
                    </div>
                </form>
            </div>

        <?php endif; ?>

    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
