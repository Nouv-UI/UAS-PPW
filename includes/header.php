<?php
// includes/header.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

$current_user = get_logged_in_user();
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $qty) {
        $cart_count += $qty;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jajan Pasar An-NaHL - Kuliner Tradisional Nusantara</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Material Icons Outlined -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <!-- Custom Style -->
    <link href="/jp-annahls/assets/css/style.css" rel="stylesheet">
</head>
<body>

    <!-- Floating Navbar Wrapper -->
    <div class="navbar-wrapper d-flex align-items-center gap-3">
        
        <!-- Logo Circle (Terpisah) -->
        <a href="/jp-annahls/index.php" class="text-decoration-none">
            <div class="logo-circle">
                <img src="/jp-annahls/assets/img/gambar_logomark.png" alt="Logo An-NaHL">
            </div>
        </a>

        <!-- Main Navigation Pill (Beranda, Katalog, Keranjang) -->
        <nav class="navbar navbar-expand-lg floating-navbar navbar-light bg-light flex-grow-1">
            <div class="container-fluid p-0 d-flex align-items-center justify-content-between">
                

                <!-- Responsive Toggle Button -->
                <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation" style="border: none; color: #7A1A1A;">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Navbar Links -->
                <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                    <ul class="navbar-nav align-items-lg-center">
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center" href="/jp-annahls/index.php">
                                <span class="material-icons-outlined me-1" style="font-size: 1.25rem;">home</span> Beranda
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center" href="/jp-annahls/pages/katalog.php">
                                <span class="material-icons-outlined me-1" style="font-size: 1.25rem;">grid_view</span> Katalog
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link position-relative me-lg-2 d-flex align-items-center" href="/jp-annahls/pages/cart.php">
                                <span class="material-icons-outlined me-1" style="font-size: 1.25rem;">shopping_cart</span> Keranjang
                                <?php if ($cart_count > 0): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.75rem;">
                                        <?= $cart_count ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                        </li>

                        <!-- Auth Links inside Collapsed Menu (Only visible on Mobile) -->
                        <li class="nav-item d-lg-none mt-2 pt-2 border-top">
                            <?php if ($current_user): ?>
                                <?php if ($current_user['role'] === 'admin'): ?>
                                    <a class="nav-link text-danger fw-bold d-flex align-items-center" href="/jp-annahls/pages/admin_dashboard.php">
                                        <span class="material-icons-outlined me-1" style="font-size: 1.25rem;">dashboard</span> Admin Panel
                                    </a>
                                <?php endif; ?>
                                <a class="nav-link d-flex align-items-center" href="/jp-annahls/pages/profile.php">
                                    <span class="material-icons-outlined me-1" style="font-size: 1.25rem;">account_circle</span> <?= esc($current_user['nama_pemesan']) ?>
                                </a>
                                <a class="btn btn-secondary-pill w-100 mt-2 d-flex align-items-center justify-content-center" href="/jp-annahls/pages/logout.php">
                                    <span class="material-icons-outlined me-1" style="font-size: 1.1rem;">logout</span> Keluar
                                </a>
                            <?php else: ?>
                                <a class="btn btn-primary-pill w-100 mt-2 d-flex align-items-center justify-content-center" href="/jp-annahls/pages/login.php">
                                    <span class="material-icons-outlined me-1" style="font-size: 1.1rem;">login</span> Masuk
                                </a>
                            <?php endif; ?>
                        </li>
                    </ul>
                </div>
                
            </div>
        </nav>

        <!-- Auth / User Pill (Only visible on Desktop) -->
        <div class="auth-navbar-pill d-none d-lg-flex gap-2 align-items-center">
            <?php if ($current_user): ?>
                <?php if ($current_user['role'] === 'admin'): ?>
                    <a class="nav-link text-danger fw-bold me-2 d-flex align-items-center" href="/jp-annahls/pages/admin_dashboard.php" style="font-size: 0.9rem;">
                        <span class="material-icons-outlined me-1" style="font-size: 1.2rem;">dashboard</span> Admin
                    </a>
                <?php endif; ?>
                <a class="nav-link me-2 fw-semibold d-flex align-items-center" href="/jp-annahls/pages/profile.php" style="color: var(--primary-color) !important; font-size: 0.9rem;">
                    <span class="material-icons-outlined me-1" style="font-size: 1.25rem;">account_circle</span> <?= esc(explode(' ', $current_user['nama_pemesan'])[0]) ?>
                </a>
                <a class="btn btn-secondary-pill py-1 px-3 d-flex align-items-center" href="/jp-annahls/pages/logout.php" style="font-size: 0.85rem;">
                    <span class="material-icons-outlined me-1" style="font-size: 1.1rem;">logout</span> Keluar
                </a>
            <?php else: ?>
                <a class="btn btn-primary-pill py-2 px-4 d-flex align-items-center" href="/jp-annahls/pages/login.php" style="font-size: 0.9rem;">
                    <span class="material-icons-outlined me-1" style="font-size: 1.1rem;">login</span> Masuk
                </a>
            <?php endif; ?>
        </div>

    </div>

    <!-- Alert Flash Messages -->
    <div class="container my-3 max-width-1200" style="max-width: 1200px;">
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="alert alert-<?= $_SESSION['flash_message']['type'] ?> alert-dismissible fade show" role="alert" style="border-radius: 15px;">
                <?= $_SESSION['flash_message']['text'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>
    </div>

    <main class="container my-4" style="max-width: 1200px;">
