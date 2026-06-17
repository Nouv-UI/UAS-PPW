<?php
// pages/admin_categories.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Protect page
require_admin();

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$error = '';
$success = '';

// Handle Delete Operation
if ($action === 'delete') {
    $category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    try {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE category_id = ?");
        $stmt->execute([$category_id]);
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'text' => 'Kategori berhasil dihapus!'
        ];
    } catch (PDOException $e) {
        $_SESSION['flash_message'] = [
            'type' => 'danger',
            'text' => 'Gagal menghapus kategori: ' . $e->getMessage()
        ];
    }
    header("Location: /jp-annahls/pages/admin_categories.php");
    exit();
}

// Handle Add/Edit Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    $category_name = trim($_POST['category_name'] ?? '');
    $category_type = trim($_POST['category_type'] ?? 'lainnya');
    $description = trim($_POST['description'] ?? '');

    if (empty($category_name)) {
        $error = 'Nama kategori wajib diisi.';
    } else {
        try {
            if ($category_id === 0) {
                // ADD NEW
                $stmt = $pdo->prepare("
                    INSERT INTO categories (category_name, category_type, description) 
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$category_name, $category_type, $description]);
            } else {
                // EDIT EXISTING
                $stmt = $pdo->prepare("
                    UPDATE categories 
                    SET category_name = ?, category_type = ?, description = ? 
                    WHERE category_id = ?
                ");
                $stmt->execute([$category_name, $category_type, $description, $category_id]);
            }

            $_SESSION['flash_message'] = [
                'type' => 'success',
                'text' => 'Data kategori berhasil disimpan!'
            ];
            header("Location: /jp-annahls/pages/admin_categories.php");
            exit();

        } catch (PDOException $e) {
            $error = 'Gagal menyimpan kategori: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold" style="color: var(--primary-color);">Manajemen Kategori</h2>
        <p class="text-muted">Kelola pengelompokan jenis jajanan pasar seperti Makanan, Minuman, dan Camilan Ringan.</p>
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
            <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 15px;">
                <i class="bi bi-exclamation-octagon-fill me-2"></i> <?= esc($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($action === 'list'): ?>
            <!-- LIST CATEGORIES VIEW -->
            <?php
            try {
                $categories = $pdo->query("SELECT * FROM categories ORDER BY category_id DESC")->fetchAll();
            } catch (PDOException $e) {
                $categories = [];
                $error = 'Gagal memuat kategori: ' . $e->getMessage();
            }
            ?>

            <div class="card custom-card p-4 border border-1">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold m-0" style="color: var(--primary-color);">Daftar Kategori</h5>
                    <a href="/jp-annahls/pages/admin_categories.php?action=add" class="btn btn-primary-pill"><i class="bi bi-plus-circle me-1"></i> Tambah Kategori Baru</a>
                </div>

                <div class="table-responsive">
                    <table class="table table-custom align-middle">
                        <thead>
                            <tr>
                                <th style="width: 10%">ID</th>
                                <th style="width: 25%">Nama Kategori</th>
                                <th style="width: 20%">Tipe Kategori</th>
                                <th style="width: 35%">Deskripsi</th>
                                <th style="width: 10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($categories)): ?>
                                <?php foreach ($categories as $c): ?>
                                    <tr>
                                        <td><strong>#<?= esc($c['category_id']) ?></strong></td>
                                        <td class="fw-bold text-dark"><?= esc($c['category_name']) ?></td>
                                        <td>
                                            <span class="badge text-uppercase rounded-pill bg-light text-dark border border-1" style="font-size:0.8rem; font-weight:600;">
                                                <?= esc($c['category_type']) ?>
                                            </span>
                                        </td>
                                        <td class="small text-muted"><?= esc($c['description'] ?: '-') ?></td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="/jp-annahls/pages/admin_categories.php?action=edit&id=<?= $c['category_id'] ?>" class="btn btn-sm btn-outline-warning d-flex align-items-center justify-content-center" title="Edit" style="width: 32px; height: 32px; padding: 0;"><span class="material-icons-outlined" style="font-size: 1.15rem;">edit</span></a>
                                                <a href="/jp-annahls/pages/admin_categories.php?action=delete&id=<?= $c['category_id'] ?>" class="btn btn-sm btn-outline-danger confirm-delete d-flex align-items-center justify-content-center" data-item-name="<?= esc($c['category_name']) ?>" title="Hapus" style="width: 32px; height: 32px; padding: 0;"><span class="material-icons-outlined" style="font-size: 1.15rem;">delete</span></a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Belum ada kategori yang ditambahkan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($action === 'add' || $action === 'edit'): ?>
            <!-- ADD / EDIT FORM VIEW -->
            <?php
            $category = [
                'category_id' => 0,
                'category_name' => '',
                'category_type' => 'makanan',
                'description' => ''
            ];

            if ($action === 'edit') {
                $category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
                try {
                    $stmt = $pdo->prepare("SELECT * FROM categories WHERE category_id = ?");
                    $stmt->execute([$category_id]);
                    $fetched_category = $stmt->fetch();
                    if ($fetched_category) {
                        $category = $fetched_category;
                    } else {
                        $error = 'Kategori tidak ditemukan.';
                        $action = 'add';
                    }
                } catch (PDOException $e) {
                    $error = 'Gagal memuat data kategori: ' . $e->getMessage();
                }
            }
            ?>

            <div class="card custom-card p-4 border border-1">
                <h5 class="fw-bold mb-4 text-uppercase" style="color: var(--primary-color);">
                    <?= $action === 'add' ? 'Tambah Kategori Baru' : 'Edit Kategori #' . $category['category_id'] ?>
                </h5>

                <form action="" method="POST" class="needs-validation <?= $action === 'edit' ? 'confirm-save-edit' : '' ?>" novalidate>
                    <input type="hidden" name="category_id" value="<?= esc($category['category_id']) ?>">

                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label for="category_name" class="form-label fw-bold">Nama Kategori <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="category_name" name="category_name" value="<?= esc($category['category_name']) ?>" required>
                            <div class="invalid-feedback">Nama kategori wajib diisi.</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="category_type" class="form-label fw-bold">Tipe Kategori <span class="text-danger">*</span></label>
                            <select class="form-select" id="category_type" name="category_type" required>
                                <option value="makanan" <?= $category['category_type'] === 'makanan' ? 'selected' : '' ?>>Makanan (Food)</option>
                                <option value="minuman" <?= $category['category_type'] === 'minuman' ? 'selected' : '' ?>>Minuman (Beverage)</option>
                                <option value="snack" <?= $category['category_type'] === 'snack' ? 'selected' : '' ?>>Snack (Camilan)</option>
                                <option value="lainnya" <?= $category['category_type'] === 'lainnya' ? 'selected' : '' ?>>Lainnya</option>
                            </select>
                            <div class="invalid-feedback">Silakan pilih tipe kategori.</div>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="description" class="form-label fw-bold">Deskripsi Kategori</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?= esc($category['description']) ?></textarea>
                        </div>
                    </div>

                    <div class="d-flex gap-3 justify-content-end mt-4" style="border-top:1px solid #ECECEC; padding-top: 20px;">
                        <a href="/jp-annahls/pages/admin_categories.php" class="btn btn-secondary-pill px-4">Batal</a>
                        <button type="submit" class="btn btn-primary-pill px-5">Simpan Kategori</button>
                    </div>
                </form>
            </div>

        <?php endif; ?>

    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
