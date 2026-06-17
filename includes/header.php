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
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom Style -->
    <link href="/jp-annahls/assets/css/style.css" rel="stylesheet">
</head>
<body>

    <!-- Floating Navbar Wrapper -->
    <div class="navbar-wrapper">
        <nav class="navbar navbar-expand-lg floating-navbar navbar-light bg-light">
            <div class="container-fluid p-0 d-flex align-items-center justify-content-between">
                
                <!-- Logo -->
                <a class="navbar-brand d-flex align-items-center me-3" href="/jp-annahls/index.php">
                    <div class="logo-circle">
                        <!-- Custom SVG Logo representing traditional snack (Klepon/Kue) -->
                        <svg viewBox="0 0 100 100" width="60" height="60" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="50" cy="50" r="45" fill="#7A1A1A"/>
                            <!-- Klepon visual -->
                            <circle cx="50" cy="45" r="22" fill="#4CAF50"/>
                            <!-- Coconut shavings white dots -->
                            <circle cx="38" cy="38" r="3" fill="#FFFFFF"/>
                            <circle cx="62" cy="38" r="3" fill="#FFFFFF"/>
                            <circle cx="50" cy="58" r="3" fill="#FFFFFF"/>
                            <circle cx="42" cy="50" r="2.5" fill="#FFFFFF"/>
                            <circle cx="58" cy="50" r="2.5" fill="#FFFFFF"/>
                            <!-- Liquid sugar drip -->
                            <path d="M48 45C48 48 52 48 52 45C52 42 48 42 48 45Z" fill="#7A1A1A"/>
                            <!-- Text overlay -->
                            <text x="50" y="80" fill="#FEFBF4" font-size="12" font-weight="bold" text-anchor="middle">An-NaHL</text>
                        </svg>
                    </div>
                    <span class="ms-2 d-none d-sm-inline fw-bold text-uppercase tracking-wider" style="color: #7A1A1A; font-size: 1.1rem;">Jajan Pasar</span>
                </a>

                <!-- Responsive Toggle Button -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation" style="border: none; color: #7A1A1A;">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Navbar Links -->
                <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                    <ul class="navbar-nav align-items-lg-center">
                        <li class="nav-item">
                            <a class="nav-link" href="/jp-annahls/index.php"><i class="bi bi-house-door"></i> Beranda</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/jp-annahls/pages/katalog.php"><i class="bi bi-grid"></i> Katalog</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link position-relative" href="/jp-annahls/pages/cart.php">
                                <i class="bi bi-cart3"></i> Keranjang
                                <?php if ($cart_count > 0): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.75rem;">
                                        <?= $cart_count ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                        </li>
                        
                        <?php if ($current_user): ?>
                            <?php if ($current_user['role'] === 'admin'): ?>
                                <li class="nav-item">
                                    <a class="nav-link text-danger fw-bold" href="/jp-annahls/pages/admin_dashboard.php"><i class="bi bi-speedometer2"></i> Admin Panel</a>
                                </li>
                            <?php endif; ?>
                            <li class="nav-item">
                                <a class="nav-link" href="/jp-annahls/pages/profile.php"><i class="bi bi-person-circle"></i> <?= esc($current_user['nama_pemesan']) ?></a>
                            </li>
                            <li class="nav-item ms-lg-2">
                                <a class="btn btn-secondary-pill py-1 px-3" href="/jp-annahls/pages/logout.php"><i class="bi bi-box-arrow-right"></i> Keluar</a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item ms-lg-2">
                                <a class="btn btn-primary-pill py-2 px-4" href="/jp-annahls/pages/login.php"><i class="bi bi-box-arrow-in-right"></i> Masuk</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
                
            </div>
        </nav>
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
