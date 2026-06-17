<?php
// pages/katalog.php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/header.php';

// Pagination and filters setup
$limit = 8;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? trim($_GET['category']) : '';

try {
    // Fetch categories for filter dropdown
    $categories_stmt = $pdo->query("SELECT category_name FROM categories ORDER BY category_name ASC");
    $categories = $categories_stmt->fetchAll();

    // Build query with dynamic search and filters
    $query_str = "SELECT * FROM view_daftar_produk WHERE 1=1";
    $params = [];

    if ($search !== '') {
        $query_str .= " AND (product_name LIKE :search OR categories LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }

    if ($category_filter !== '') {
        $query_str .= " AND FIND_IN_SET(:category, REPLACE(categories, ', ', ',')) > 0";
        $params[':category'] = $category_filter;
    }

    // Get total rows count for pagination
    $count_stmt = $pdo->prepare(str_replace("SELECT *", "SELECT COUNT(*)", $query_str));
    $count_stmt->execute($params);
    $total_items = $count_stmt->fetchColumn();
    $total_pages = ceil($total_items / $limit);
    if ($total_pages < 1) $total_pages = 1;
    if ($page > $total_pages) $page = $total_pages;

    // Fetch actual products
    $query_str .= " ORDER BY product_name ASC LIMIT :offset, :limit";
    $stmt = $pdo->prepare($query_str);
    
    // Bind parameters manually to handle integer types for limit/offset in PDO
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $products = [];
    $categories = [];
    $total_items = 0;
    $total_pages = 1;
    $error = 'Gagal memuat produk: ' . $e->getMessage();
}
?>

<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/jp-annahls/index.php" style="color: var(--primary-color);">Beranda</a></li>
                <li class="breadcrumb-item active" aria-current="page">Katalog Produk</li>
            </ol>
        </nav>
        <h2 class="fw-bold" style="color: var(--primary-color);">Katalog Jajanan Pasar</h2>
        <p class="text-muted">Temukan rasa jajanan pasar kesukaan Anda yang terjamin rasa dan kualitasnya.</p>
    </div>
</div>

<!-- Search and Filters Section -->
<div class="card custom-card p-4 border border-1 mb-5">
    <form action="" method="GET" class="row g-3">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control border-start-0 ps-0" name="search" value="<?= esc($search) ?>" placeholder="Cari nama produk atau kategori...">
            </div>
        </div>
        <div class="col-md-4">
            <select class="form-select" name="category">
                <option value="">Semua Kategori</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= esc($cat['category_name']) ?>" <?= $category_filter === $cat['category_name'] ? 'selected' : '' ?>>
                        <?= esc($cat['category_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary-pill w-100 py-2"><i class="bi bi-funnel"></i> Filter</button>
        </div>
    </form>
</div>

<!-- Products Grid -->
<div class="row g-4">
    <?php if (isset($error)): ?>
        <div class="col-12">
            <div class="alert alert-danger" role="alert">
                <?= esc($error) ?>
            </div>
        </div>
    <?php elseif (!empty($products)): ?>
        <?php foreach ($products as $prod): ?>
            <div class="col-lg-3 col-md-6">
                <div class="card custom-card h-100">
                    <div class="position-relative d-flex align-items-center justify-content-center" style="height: 200px; background-color: var(--placeholder-bg);">
                        <?php if (!empty($prod['image_url'])): ?>
                            <img src="/jp-annahls/assets/img/<?= esc($prod['image_url']) ?>" class="card-img-top w-100 h-100" alt="<?= esc($prod['product_name']) ?>" style="object-fit: cover;">
                        <?php else: ?>
                            <div class="text-center text-muted">
                                <i class="bi bi-image fs-1 opacity-50"></i>
                                <p class="small mb-0 mt-1"><?= esc($prod['product_name']) ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Categories Badges -->
                        <div class="position-absolute top-0 start-0 m-2 d-flex flex-wrap gap-1">
                            <?php 
                            $prod_cats = explode(', ', $prod['categories']);
                            foreach ($prod_cats as $p_cat):
                            ?>
                                <span class="badge badge-primary rounded-pill" style="font-size: 0.75rem;">
                                    <?= esc($p_cat) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="card-title fw-bold" style="color: var(--primary-color);"><?= esc($prod['product_name']) ?></h5>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small text-muted"><i class="bi bi-box-seam"></i> Stok: <?= esc($prod['stock']) ?></span>
                                <?php if ($prod['stock'] > 0): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Tersedia</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">Habis</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mt-3">
                            <span class="fw-bold text-dark fs-5"><?= format_rupiah($prod['harga_jual']) ?></span>
                            <a href="/jp-annahls/pages/detail.php?id=<?= $prod['product_id'] ?>" class="btn btn-tertiary-rounded py-1 px-3">
                                Detail
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12 text-center py-5">
            <i class="bi bi-search fs-1 text-muted opacity-50"></i>
            <p class="text-muted mt-3 fs-5">Tidak ditemukan produk yang cocok dengan pencarian Anda.</p>
            <a href="/jp-annahls/pages/katalog.php" class="btn btn-secondary-pill px-4 mt-2">Reset Filter</a>
        </div>
    <?php endif; ?>
</div>

<!-- Pagination Section -->
<?php if ($total_pages > 1): ?>
    <div class="row mt-5">
        <div class="col-12 d-flex justify-content-center">
            <nav aria-label="Halaman katalog">
                <ul class="pagination">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category_filter) ?>" aria-label="Previous" style="color: var(--primary-color);">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= $page === $i ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category_filter) ?>" style="<?= $page === $i ? 'background-color: var(--primary-color); border-color: var(--primary-color); color: #FFF;' : 'color: var(--primary-color);' ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category_filter) ?>" aria-label="Next" style="color: var(--primary-color);">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
<?php endif; ?>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
