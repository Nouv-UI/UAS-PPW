<?php
// pages/admin_products.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Protect page
require_admin();

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$error = '';
$success = '';

// Handle Delete Operation
if ($action === 'delete') {
    $product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'text' => 'Produk berhasil dihapus!'
        ];
    } catch (PDOException $e) {
        $_SESSION['flash_message'] = [
            'type' => 'danger',
            'text' => 'Gagal menghapus produk: ' . $e->getMessage()
        ];
    }
    header("Location: /jp-annahls/pages/admin_products.php");
    exit();
}

// Handle Add/Edit Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $product_name = trim($_POST['product_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $supplier_id = !empty($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null;
    $harga_supplier = (float)($_POST['harga_supplier'] ?? 0);
    $harga_jual = (float)($_POST['harga_jual'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $image_url = trim($_POST['image_url'] ?? '');
    $category_ids = isset($_POST['categories']) ? $_POST['categories'] : [];

    if (empty($product_name)) {
        $error = 'Nama produk wajib diisi.';
    } else {
        try {
            $pdo->beginTransaction();

            if ($product_id === 0) {
                // ADD NEW
                $stmt = $pdo->prepare("
                    INSERT INTO products (product_name, supplier_id, description, harga_supplier, harga_jual, stock, image_url, is_active, created_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?)
                ");
                $stmt->execute([$product_name, $supplier_id, $description, $harga_supplier, $harga_jual, $stock, $image_url === '' ? null : $image_url, $_SESSION['username']]);
                $product_id = $pdo->lastInsertId();
            } else {
                // EDIT EXISTING
                $stmt = $pdo->prepare("
                    UPDATE products 
                    SET product_name = ?, supplier_id = ?, description = ?, harga_supplier = ?, harga_jual = ?, stock = ?, image_url = ?
                    WHERE product_id = ?
                ");
                $stmt->execute([$product_name, $supplier_id, $description, $harga_supplier, $harga_jual, $stock, $image_url === '' ? null : $image_url, $product_id]);
            }

            // Sync product categories (delete old, insert new)
            $del_stmt = $pdo->prepare("DELETE FROM product_categories WHERE product_id = ?");
            $del_stmt->execute([$product_id]);

            if (!empty($category_ids)) {
                $ins_cat_stmt = $pdo->prepare("INSERT INTO product_categories (product_id, category_id) VALUES (?, ?)");
                foreach ($category_ids as $cat_id) {
                    $ins_cat_stmt->execute([$product_id, $cat_id]);
                }
            }

            $pdo->commit();
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'text' => 'Data produk berhasil disimpan!'
            ];
            header("Location: /jp-annahls/pages/admin_products.php");
            exit();

        } catch (PDOException $e) {
            $pdo->rollBack();
            // Catch error thrown by database trigger validasi_harga_products_insert or validasi_harga_products_update
            if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1644) {
                // State 45000 custom trigger error message
                $error = 'Gagal menyimpan data: ' . $e->errorInfo[2];
            } else {
                $error = 'Gagal menyimpan produk: ' . $e->getMessage();
            }
        }
    }
}

// Fetch lists of suppliers & categories for forms
try {
    $suppliers = $pdo->query("SELECT * FROM suppliers WHERE is_active = 1 ORDER BY supplier_name ASC")->fetchAll();
    $categories = $pdo->query("SELECT * FROM categories ORDER BY category_name ASC")->fetchAll();
} catch (PDOException $e) {
    $suppliers = [];
    $categories = [];
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="fw-bold" style="color: var(--primary-color);">Manajemen Produk</h2>
        <p class="text-muted">Kelola persediaan jajanan pasar, perbarui harga, kategori, dan detail pemasok.</p>
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
            <!-- LIST PRODUCTS VIEW -->
            <?php
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';
            $limit = 10;
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            if ($page < 1) $page = 1;
            $offset = ($page - 1) * $limit;

            try {
                $query_str = "
                    SELECT p.*, s.supplier_name, GROUP_CONCAT(c.category_name SEPARATOR ', ') AS categories_list
                    FROM products p
                    LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
                    LEFT JOIN product_categories pc ON p.product_id = pc.product_id
                    LEFT JOIN categories c ON pc.category_id = c.category_id
                    WHERE 1=1
                ";
                $params = [];

                if ($search !== '') {
                    $query_str .= " AND (p.product_name LIKE :search OR s.supplier_name LIKE :search OR c.category_name LIKE :search)";
                    $params[':search'] = '%' . $search . '%';
                }

                $query_str .= " GROUP BY p.product_id";

                // Count total for pagination
                $count_stmt = $pdo->prepare("SELECT COUNT(DISTINCT p.product_id) FROM products p LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id LEFT JOIN product_categories pc ON p.product_id = pc.product_id LEFT JOIN categories c ON pc.category_id = c.category_id WHERE 1=1 " . ($search !== '' ? "AND (p.product_name LIKE :search OR s.supplier_name LIKE :search OR c.category_name LIKE :search)" : ""));
                $count_stmt->execute($params);
                $total_items = $count_stmt->fetchColumn();
                $total_pages = ceil($total_items / $limit);
                if ($total_pages < 1) $total_pages = 1;
                if ($page > $total_pages) $page = $total_pages;

                // Fetch data
                $query_str .= " ORDER BY p.product_id DESC LIMIT :offset, :limit";
                $stmt = $pdo->prepare($query_str);
                
                foreach ($params as $key => $val) {
                    $stmt->bindValue($key, $val);
                }
                $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
                $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
                $stmt->execute();
                $products = $stmt->fetchAll();

            } catch (PDOException $e) {
                $products = [];
                $total_pages = 1;
                $error = 'Gagal memuat daftar produk: ' . $e->getMessage();
            }
            ?>

            <div class="card custom-card p-4 border border-1">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                    <form action="" method="GET" class="d-flex gap-2" style="max-width: 400px; flex: 1;">
                        <input type="hidden" name="action" value="list">
                        <input type="text" class="form-control" name="search" value="<?= esc($search) ?>" placeholder="Cari produk...">
                        <button type="submit" class="btn btn-secondary-pill px-3 py-1">Cari</button>
                    </form>
                    <a href="/jp-annahls/pages/admin_products.php?action=add" class="btn btn-primary-pill"><i class="bi bi-plus-circle me-1"></i> Tambah Produk Baru</a>
                </div>

                <div class="table-responsive">
                    <table class="table table-custom align-middle">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Kategori</th>
                                <th>Supplier</th>
                                <th>Harga Supplier</th>
                                <th>Harga Jual</th>
                                <th>Stok</th>
                                <th style="width: 15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($products)): ?>
                                <?php foreach ($products as $p): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div style="width: 45px; height: 45px; background-color: var(--placeholder-bg); border-radius: 8px; overflow: hidden; display:flex; align-items:center; justify-content:center;" class="me-2">
                                                    <?php if (!empty($p['image_url'])): ?>
                                                        <img src="/jp-annahls/assets/img/<?= esc($p['image_url']) ?>" class="w-100 h-100" style="object-fit: cover;">
                                                    <?php else: ?>
                                                        <span class="material-icons-outlined text-muted opacity-50" style="font-size: 1.5rem;">image</span>
                                                    <?php endif; ?>
                                                </div>
                                                <span class="fw-bold text-dark"><?= esc($p['product_name']) ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <?php 
                                            $p_cats = explode(', ', $p['categories_list'] ?? '');
                                            foreach ($p_cats as $cat):
                                                if (empty($cat)) continue;
                                            ?>
                                                <span class="badge badge-outline-primary rounded-pill mb-1"><?= esc($cat) ?></span>
                                            <?php endforeach; ?>
                                        </td>
                                        <td class="small"><?= esc($p['supplier_name'] ?: 'Internal') ?></td>
                                        <td><?= format_rupiah($p['harga_supplier']) ?></td>
                                        <td class="fw-bold" style="color: var(--primary-color);"><?= format_rupiah($p['harga_jual']) ?></td>
                                        <td>
                                            <?php if ($p['stock'] <= 5): ?>
                                                <span class="text-danger fw-bold"><span class="material-icons-outlined" style="font-size: 1.1rem; vertical-align: middle;">warning</span> <?= esc($p['stock']) ?></span>
                                            <?php else: ?>
                                                <?= esc($p['stock']) ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="/jp-annahls/pages/admin_products.php?action=edit&id=<?= $p['product_id'] ?>" class="btn btn-sm btn-outline-warning d-flex align-items-center justify-content-center" title="Edit" style="width: 32px; height: 32px; padding: 0;"><span class="material-icons-outlined" style="font-size: 1.15rem;">edit</span></a>
                                                <a href="/jp-annahls/pages/admin_products.php?action=delete&id=<?= $p['product_id'] ?>" class="btn btn-sm btn-outline-danger confirm-delete d-flex align-items-center justify-content-center" data-item-name="<?= esc($p['product_name']) ?>" title="Hapus" style="width: 32px; height: 32px; padding: 0;"><span class="material-icons-outlined" style="font-size: 1.15rem;">delete</span></a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Tidak ada produk ditemukan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="d-flex justify-content-center mt-4">
                        <nav aria-label="Page navigation">
                            <ul class="pagination">
                                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?action=list&page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>" aria-label="Previous" style="color: var(--primary-color);">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?= $page === $i ? 'active' : '' ?>">
                                        <a class="page-link" href="?action=list&page=<?= $i ?>&search=<?= urlencode($search) ?>" style="<?= $page === $i ? 'background-color: var(--primary-color); border-color: var(--primary-color); color: #FFF;' : 'color: var(--primary-color);' ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?action=list&page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>" aria-label="Next" style="color: var(--primary-color);">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ($action === 'add' || $action === 'edit'): ?>
            <!-- ADD / EDIT FORM VIEW -->
            <?php
            $product = [
                'product_id' => 0,
                'product_name' => '',
                'supplier_id' => '',
                'description' => '',
                'harga_supplier' => 0.00,
                'harga_jual' => 0.00,
                'stock' => 0,
                'image_url' => ''
            ];
            $linked_categories = [];

            if ($action === 'edit') {
                $product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
                try {
                    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
                    $stmt->execute([$product_id]);
                    $fetched_product = $stmt->fetch();
                    if ($fetched_product) {
                        $product = $fetched_product;

                        // Fetch linked categories
                        $cat_stmt = $pdo->prepare("SELECT category_id FROM product_categories WHERE product_id = ?");
                        $cat_stmt->execute([$product_id]);
                        $linked_categories = $cat_stmt->fetchAll(PDO::FETCH_COLUMN);
                    } else {
                        $error = 'Produk tidak ditemukan.';
                        $action = 'add';
                    }
                } catch (PDOException $e) {
                    $error = 'Gagal memuat produk: ' . $e->getMessage();
                }
            }
            ?>

            <div class="card custom-card p-4 border border-1">
                <h5 class="fw-bold mb-4 text-uppercase" style="color: var(--primary-color);">
                    <?= $action === 'add' ? 'Tambah Produk Baru' : 'Edit Produk #' . $product['product_id'] ?>
                </h5>

                <form action="" method="POST" class="needs-validation <?= $action === 'edit' ? 'confirm-save-edit' : '' ?>" novalidate>
                    <input type="hidden" name="product_id" value="<?= esc($product['product_id']) ?>">

                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label for="product_name" class="form-label fw-bold">Nama Produk <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="product_name" name="product_name" value="<?= esc($product['product_name']) ?>" required>
                            <div class="invalid-feedback">Nama produk wajib diisi.</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="supplier_id" class="form-label fw-bold">Pemasok (Supplier)</label>
                            <select class="form-select" id="supplier_id" name="supplier_id">
                                <option value="">-- Pilih Pemasok --</option>
                                <?php foreach ($suppliers as $s): ?>
                                    <option value="<?= $s['supplier_id'] ?>" <?= $product['supplier_id'] == $s['supplier_id'] ? 'selected' : '' ?>>
                                        <?= esc($s['supplier_name']) ?> (<?= esc($s['city']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="description" class="form-label fw-bold">Deskripsi Produk</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?= esc($product['description']) ?></textarea>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="harga_supplier" class="form-label fw-bold">Harga Supplier <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="harga_supplier" name="harga_supplier" value="<?= esc((float)$product['harga_supplier']) ?>" min="0" step="100" required>
                                <div class="invalid-feedback">Harga supplier tidak boleh negatif.</div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="harga_jual" class="form-label fw-bold">Harga Jual <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="harga_jual" name="harga_jual" value="<?= esc((float)$product['harga_jual']) ?>" min="0" step="100" required>
                                <div class="invalid-feedback" id="harga_jual_feedback">Harga jual tidak boleh negatif.</div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="stock" class="form-label fw-bold">Stok <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="stock" name="stock" value="<?= esc($product['stock']) ?>" min="0" required>
                            <div class="invalid-feedback">Stok tidak boleh negatif.</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="image_url" class="form-label fw-bold">File Nama Gambar / URL</label>
                            <input type="text" class="form-control" id="image_url" name="image_url" value="<?= esc($product['image_url']) ?>" placeholder="Contoh: klepon.jpg">
                            <span class="text-muted small">Letakkan file gambar di folder `assets/img/` lalu masukkan nama filenya saja.</span>
                        </div>

                        <!-- Categories checkboxes -->
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-bold d-block">Pilih Kategori Produk</label>
                            <div class="row g-2">
                                <?php if (!empty($categories)): ?>
                                    <?php foreach ($categories as $cat): ?>
                                        <div class="col-sm-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="categories[]" value="<?= $cat['category_id'] ?>" id="cat_<?= $cat['category_id'] ?>" <?= in_array($cat['category_id'], $linked_categories) ? 'checked' : '' ?>>
                                                <label class="form-check-label text-dark" for="cat_<?= $cat['category_id'] ?>">
                                                    <?= esc($cat['category_name']) ?> <span class="text-muted text-uppercase" style="font-size:0.75rem;">(<?= esc($cat['category_type']) ?>)</span>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="col-12"><p class="text-muted small">Kategori belum tersedia.</p></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-3 justify-content-end mt-4" style="border-top:1px solid #ECECEC; padding-top: 20px;">
                        <a href="/jp-annahls/pages/admin_products.php" class="btn btn-secondary-pill px-4">Batal</a>
                        <button type="submit" class="btn btn-primary-pill px-5">Simpan Data</button>
                    </div>
                </form>
            </div>

        <?php endif; ?>

    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
