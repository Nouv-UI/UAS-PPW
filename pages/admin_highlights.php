<?php
// pages/admin_highlights.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Protect page
require_admin();

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$error = '';
$success = '';

// Handle Delete Operation
if ($action === 'delete') {
    $highlight_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    try {
        $stmt = $pdo->prepare("DELETE FROM highlight WHERE highlight_id = ?");
        $stmt->execute([$highlight_id]);
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'text' => 'Highlight berhasil dihapus!'
        ];
    } catch (PDOException $e) {
        $_SESSION['flash_message'] = [
            'type' => 'danger',
            'text' => 'Gagal menghapus highlight: ' . $e->getMessage()
        ];
    }
    header("Location: /jp-annahls/pages/admin_highlights.php");
    exit();
}

// Handle Add/Edit Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $highlight_id = isset($_POST['highlight_id']) ? (int)$_POST['highlight_id'] : 0;
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $fun_fact = trim($_POST['fun_fact'] ?? '');
    $start_date = trim($_POST['start_date'] ?? '');
    $end_date = trim($_POST['end_date'] ?? '');

    if ($product_id === 0 || empty($fun_fact) || empty($start_date) || empty($end_date)) {
        $error = 'Semua kolom formulir wajib diisi.';
    } elseif (strtotime($end_date) < strtotime($start_date)) {
        $error = 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.';
    } else {
        try {
            if ($highlight_id === 0) {
                // ADD NEW
                $stmt = $pdo->prepare("
                    INSERT INTO highlight (product_id, fun_fact, start_date, end_date) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$product_id, $fun_fact, $start_date, $end_date]);
            } else {
                // EDIT EXISTING
                $stmt = $pdo->prepare("
                    UPDATE highlight 
                    SET product_id = ?, fun_fact = ?, start_date = ?, end_date = ? 
                    WHERE highlight_id = ?
                ");
                $stmt->execute([$product_id, $fun_fact, $start_date, $end_date, $highlight_id]);
            }

            $_SESSION['flash_message'] = [
                'type' => 'success',
                'text' => 'Data highlight berhasil disimpan!'
            ];
            header("Location: /jp-annahls/pages/admin_highlights.php");
            exit();

        } catch (PDOException $e) {
            $error = 'Gagal menyimpan highlight: ' . $e->getMessage();
        }
    }
}

// Fetch list of products for add/edit dropdown
try {
    $products = $pdo->query("SELECT product_id, product_name FROM products WHERE is_active = 1 ORDER BY product_name ASC")->fetchAll();
} catch (PDOException $e) {
    $products = [];
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold" style="color: var(--primary-color);">Manajemen Highlight Produk</h2>
        <p class="text-muted">Kelola cerita fakta unik produk ("fun facts") untuk memikat pembeli di halaman beranda utama.</p>
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
                <a href="/jp-annahls/pages/admin_highlights.php" class="list-group-item list-group-item-action active"><i class="bi bi-stars me-2"></i> Manajemen Highlight</a>
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
            <!-- LIST HIGHLIGHTS VIEW -->
            <?php
            try {
                // Fetch all highlights with product details
                $highlights = $pdo->query("
                    SELECT h.*, p.product_name 
                    FROM highlight h
                    JOIN products p ON h.product_id = p.product_id
                    ORDER BY h.highlight_id DESC
                ")->fetchAll();
            } catch (PDOException $e) {
                $highlights = [];
                $error = 'Gagal memuat highlight: ' . $e->getMessage();
            }
            ?>

            <div class="card custom-card p-4 border border-1">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold m-0" style="color: var(--primary-color);">Daftar Cerita Highlight</h5>
                    <a href="/jp-annahls/pages/admin_highlights.php?action=add" class="btn btn-primary-pill"><i class="bi bi-plus-circle me-1"></i> Tambah Highlight Baru</a>
                </div>

                <div class="table-responsive">
                    <table class="table table-custom align-middle">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Fun Fact</th>
                                <th>Tanggal Mulai</th>
                                <th>Tanggal Selesai</th>
                                <th>Status Tampil</th>
                                <th class="text-center" style="width: 10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($highlights)): ?>
                                <?php foreach ($highlights as $h): ?>
                                    <?php 
                                    $today = date('Y-m-d');
                                    $is_active = ($today >= $h['start_date'] && $today <= $h['end_date']);
                                    ?>
                                    <tr>
                                        <td><strong class="text-dark"><?= esc($h['product_name']) ?></strong></td>
                                        <td class="small" style="max-width: 300px; line-height: 1.5;">"<?= esc($h['fun_fact']) ?>"</td>
                                        <td><?= esc(date('d M Y', strtotime($h['start_date']))) ?></td>
                                        <td><?= esc(date('d M Y', strtotime($h['end_date']))) ?></td>
                                        <td>
                                            <?php if ($is_active): ?>
                                                <span class="badge bg-success text-white rounded-pill px-3 py-1">Aktif Tampil</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary text-white rounded-pill px-3 py-1">Tidak Aktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="/jp-annahls/pages/admin_highlights.php?action=edit&id=<?= $h['highlight_id'] ?>" class="btn btn-sm btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                                                <a href="/jp-annahls/pages/admin_highlights.php?action=delete&id=<?= $h['highlight_id'] ?>" class="btn btn-sm btn-outline-danger confirm-delete" data-item-name="highlight <?= esc($h['product_name']) ?>" title="Hapus"><i class="bi bi-trash"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">Belum ada highlight produk yang ditambahkan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($action === 'add' || $action === 'edit'): ?>
            <!-- ADD / EDIT FORM VIEW -->
            <?php
            $highlight = [
                'highlight_id' => 0,
                'product_id' => '',
                'fun_fact' => '',
                'start_date' => date('Y-m-d'),
                'end_date' => date('Y-m-d', strtotime('+7 days'))
            ];

            if ($action === 'edit') {
                $highlight_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
                try {
                    $stmt = $pdo->prepare("SELECT * FROM highlight WHERE highlight_id = ?");
                    $stmt->execute([$highlight_id]);
                    $fetched_highlight = $stmt->fetch();
                    if ($fetched_highlight) {
                        $highlight = $fetched_highlight;
                    } else {
                        $error = 'Highlight tidak ditemukan.';
                        $action = 'add';
                    }
                } catch (PDOException $e) {
                    $error = 'Gagal memuat data highlight: ' . $e->getMessage();
                }
            }
            ?>

            <div class="card custom-card p-4 border border-1">
                <h5 class="fw-bold mb-4 text-uppercase" style="color: var(--primary-color);">
                    <?= $action === 'add' ? 'Tambah Highlight Baru' : 'Edit Highlight #' . $highlight['highlight_id'] ?>
                </h5>

                <form action="" method="POST" class="needs-validation <?= $action === 'edit' ? 'confirm-save-edit' : '' ?>" novalidate>
                    <input type="hidden" name="highlight_id" value="<?= esc($highlight['highlight_id']) ?>">

                    <div class="row g-3">
                        <div class="col-md-12 mb-3">
                            <label for="product_id" class="form-label fw-bold">Pilih Produk <span class="text-danger">*</span></label>
                            <select class="form-select" id="product_id" name="product_id" required>
                                <option value="">-- Pilih Produk --</option>
                                <?php foreach ($products as $p): ?>
                                    <option value="<?= $p['product_id'] ?>" <?= $highlight['product_id'] == $p['product_id'] ? 'selected' : '' ?>>
                                        <?= esc($p['product_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Silakan pilih produk.</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label fw-bold">Tanggal Mulai Tampil <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?= esc($highlight['start_date']) ?>" required>
                            <div class="invalid-feedback">Tanggal mulai wajib diisi.</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label fw-bold">Tanggal Selesai Tampil <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="<?= esc($highlight['end_date']) ?>" required>
                            <div class="invalid-feedback">Tanggal selesai wajib diisi.</div>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="fun_fact" class="form-label fw-bold">Cerita Unik / Cerita Sejarah ("Fun Fact") <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="fun_fact" name="fun_fact" rows="4" placeholder="Tuliskan cerita menarik tentang asal-usul kue, bahan pembuatan, atau nama produk..." required><?= esc($highlight['fun_fact']) ?></textarea>
                            <div class="invalid-feedback">Fun fact wajib diisi.</div>
                        </div>
                    </div>

                    <div class="d-flex gap-3 justify-content-end mt-4" style="border-top:1px solid #ECECEC; padding-top: 20px;">
                        <a href="/jp-annahls/pages/admin_highlights.php" class="btn btn-secondary-pill px-4">Batal</a>
                        <button type="submit" class="btn btn-primary-pill px-5">Simpan Highlight</button>
                    </div>
                </form>
            </div>

        <?php endif; ?>

    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
