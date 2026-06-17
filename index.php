<?php
// index.php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/header.php';

// Fetch highlight products using view_highlight_ditampilkan
try {
    $stmt_highlights = $pdo->query("SELECT * FROM view_highlight_ditampilkan LIMIT 3");
    $highlights = $stmt_highlights->fetchAll();
} catch (PDOException $e) {
    $highlights = [];
}

// Fetch featured products using view_daftar_produk
try {
    $stmt_featured = $pdo->query("SELECT * FROM view_daftar_produk LIMIT 4");
    $featured_products = $stmt_featured->fetchAll();
} catch (PDOException $e) {
    $featured_products = [];
}
?>

<!-- Hero Section -->
<div class="row align-items-center py-5 mb-5">
    <div class="col-lg-6 mb-4 mb-lg-0">
        <h1 class="display-4 fw-bold mb-3" style="color: var(--primary-color);">Cita Rasa Tradisional,<br>Kemasan Modern</h1>
        <p class="lead text-muted mb-4" style="font-size: 1.1rem; line-height: 1.8;">
            Selamat datang di <strong>Jajan Pasar An-NaHL</strong>. Kami menghadirkan aneka jajanan pasar khas Nusantara yang dibuat dengan bahan alami premium, tanpa pengawet, dan cinta rasa autentik.
        </p>
        <div class="d-flex flex-wrap gap-3">
            <a href="/jp-annahls/pages/katalog.php" class="btn btn-primary-pill px-4 py-3"><i class="bi bi-bag-plus me-2"></i> Belanja Sekarang</a>
            <a href="#about" class="btn btn-secondary-pill px-4 py-3"><i class="bi bi-info-circle me-2"></i> Tentang Kami</a>
        </div>
    </div>
    <div class="col-lg-6 text-center">
        <!-- Hero Illustration using styled divs -->
        <div class="position-relative d-inline-block">
            <div class="rounded-circle overflow-hidden shadow-lg mx-auto" style="width: 320px; height: 320px; border: 8px solid var(--navbar-bg);">
                <svg viewBox="0 0 100 100" class="w-100 h-100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="100" height="100" fill="#FEFBF4"/>
                    <circle cx="50" cy="50" r="40" fill="#FFF9DB"/>
                    <!-- Banana leaf plate representation -->
                    <path d="M10 50 C20 20, 80 20, 90 50 C80 80, 20 80, 10 50 Z" fill="#4CAF50"/>
                    <path d="M15 50 C25 28, 75 28, 85 50 C75 72, 25 72, 15 50 Z" fill="#81C784"/>
                    <!-- Indonesian snacks drawings (Klepon, Onde-onde) -->
                    <!-- Klepons -->
                    <circle cx="40" cy="42" r="10" fill="#2E7D32"/>
                    <circle cx="40" cy="42" r="10" stroke="#FFFFFF" stroke-dasharray="2 2"/>
                    <circle cx="60" cy="42" r="10" fill="#2E7D32"/>
                    <circle cx="60" cy="42" r="10" stroke="#FFFFFF" stroke-dasharray="2 2"/>
                    <!-- Onde-onde -->
                    <circle cx="50" cy="58" r="11" fill="#FFB74D"/>
                    <circle cx="50" cy="58" r="11" stroke="#FFFFFF" stroke-dasharray="3 1"/>
                    <!-- Gethuk / Kue lapis representation -->
                    <rect x="30" y="48" width="12" height="6" fill="#E91E63"/>
                    <rect x="30" y="54" width="12" height="6" fill="#FFF9DB"/>
                </svg>
            </div>
            <!-- Decorative badge -->
            <div class="position-absolute bottom-0 start-0 bg-white shadow p-3 rounded-4 border border-1" style="max-width: 180px; transform: rotate(-5deg);">
                <p class="small fw-bold mb-1 text-uppercase" style="color: var(--primary-color);"><i class="bi bi-patch-check-fill"></i> 100% Halal</p>
                <p class="small text-muted mb-0" style="font-size: 0.75rem;">Bahan alami pilihan tanpa pengawet.</p>
            </div>
        </div>
    </div>
</div>

<!-- Highlight Products Section -->
<?php if (!empty($highlights)): ?>
<div class="py-5 mb-5">
    <div class="text-center mb-4">
        <h2 class="fw-bold" style="color: var(--primary-color);">Rekomendasi Hari Ini</h2>
        <p class="text-muted">Tahukah Anda kisah unik di balik jajanan pasar favorit Anda?</p>
    </div>
    <div class="row g-4 justify-content-center">
        <?php foreach ($highlights as $hl): ?>
            <div class="col-md-4">
                <div class="custom-card h-100 p-4 border border-1 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center mb-3">
                            <div class="p-2 rounded-circle me-3" style="background-color: var(--navbar-bg); color: var(--primary-color);">
                                <i class="bi bi-star-fill fs-4"></i>
                            </div>
                            <h4 class="fw-bold m-0" style="color: var(--primary-color);"><?= esc($hl['product_name']) ?></h4>
                        </div>
                        <p class="text-muted italic" style="font-size: 0.95rem; line-height: 1.6; font-style: italic;">
                            "<?= esc($hl['fun_fact']) ?>"
                        </p>
                    </div>
                    <div class="mt-3">
                        <a href="/jp-annahls/pages/katalog.php" class="btn btn-tertiary-rounded w-100">
                            Lihat di Katalog <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Featured Products Section -->
<div class="py-5 mb-5">
    <div class="text-center mb-4">
        <h2 class="fw-bold" style="color: var(--primary-color);">Produk Terpopuler</h2>
        <p class="text-muted">Jajanan pasar favorit pelanggan setia kami yang selalu dicari setiap hari.</p>
    </div>
    <div class="row g-4">
        <?php if (!empty($featured_products)): ?>
            <?php foreach ($featured_products as $prod): ?>
                <div class="col-lg-3 col-md-6">
                    <div class="card custom-card h-100">
                        <!-- Product Image Placeholder -->
                        <div class="position-relative d-flex align-items-center justify-content-center" style="height: 200px; background-color: var(--placeholder-bg);">
                            <?php if (!empty($prod['image_url'])): ?>
                                <img src="/jp-annahls/assets/img/<?= esc($prod['image_url']) ?>" class="card-img-top w-100 h-100" alt="<?= esc($prod['product_name']) ?>" style="object-fit: cover;">
                            <?php else: ?>
                                <div class="text-center text-muted">
                                    <i class="bi bi-image fs-1 opacity-50"></i>
                                    <p class="small mb-0 mt-1"><?= esc($prod['product_name']) ?></p>
                                </div>
                            <?php endif; ?>
                            <!-- Category Badge -->
                            <span class="position-absolute top-0 end-0 m-3 badge rounded-pill badge-primary">
                                <?= esc(explode(', ', $prod['categories'])[0]) ?>
                            </span>
                        </div>
                        <div class="card-body d-flex flex-column justify-content-between">
                            <div>
                                <h5 class="card-title fw-bold" style="color: var(--primary-color);"><?= esc($prod['product_name']) ?></h5>
                                <p class="text-muted small mb-2"><i class="bi bi-box-seam me-1"></i> Stok: <?= esc($prod['stock']) ?> unit</p>
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
            <div class="col-12 text-center py-4">
                <p class="text-muted">Belum ada produk yang ditambahkan.</p>
            </div>
        <?php endif; ?>
    </div>
    <div class="text-center mt-5">
        <a href="/jp-annahls/pages/katalog.php" class="btn btn-primary-pill px-5 py-3"><i class="bi bi-collection me-2"></i> Lihat Semua Produk</a>
    </div>
</div>

<!-- About Section using Outlined Containers -->
<div id="about" class="py-5 mb-5">
    <div class="outlined-container">
        <div class="row align-items-center">
            <div class="col-lg-5 mb-4 mb-lg-0 text-center">
                <h3 class="display-6 fw-bold mb-3" style="color: var(--primary-color);">Tentang An-NaHL</h3>
                <div class="mx-auto" style="width: 120px; height: 4px; background-color: var(--primary-color); border-radius: 2px;"></div>
                <p class="mt-4 text-muted" style="font-size: 0.95rem; line-height: 1.8;">
                    Didirikan dengan tekad untuk melestarikan warisan kuliner Nusantara. Nama <strong>An-NaHL</strong> terinspirasi dari filosofi lebah yang selalu menghasilkan kebaikan yang bermanfaat bagi sekitar.
                </p>
            </div>
            <div class="col-lg-7">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="p-3 bg-white rounded-4 shadow-sm border border-1 h-100">
                            <h5 class="fw-bold" style="color: var(--primary-color);"><i class="bi bi-gem me-2"></i> Bahan Pilihan</h5>
                            <p class="small text-muted mb-0">Kami hanya menggunakan gula merah murni, santan kelapa segar, dan pewarna alami pandan asli.</p>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 bg-white rounded-4 shadow-sm border border-1 h-100">
                            <h5 class="fw-bold" style="color: var(--primary-color);"><i class="bi bi-clock me-2"></i> Selalu Segar</h5>
                            <p class="small text-muted mb-0">Semua jajanan pasar kami diproduksi setiap hari demi menjaga kesegaran dan rasa prima.</p>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 bg-white rounded-4 shadow-sm border border-1 h-100">
                            <h5 class="fw-bold" style="color: var(--primary-color);"><i class="bi bi-emoji-smile me-2"></i> Ramah & Higienis</h5>
                            <p class="small text-muted mb-0">Proses pengolahan hingga pengemasan dilakukan secara bersih dan mengikuti standar kesehatan.</p>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 bg-white rounded-4 shadow-sm border border-1 h-100">
                            <h5 class="fw-bold" style="color: var(--primary-color);"><i class="bi bi-percent me-2"></i> Harga Terjangkau</h5>
                            <p class="small text-muted mb-0">Menikmati cita rasa premium dengan harga yang tetap bersahabat untuk semua kalangan.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
