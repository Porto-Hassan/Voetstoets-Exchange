<?php
// ============================================================
// includes/header.php — Shared site header and navigation
// ============================================================
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/auth.php';

// Fetch categories for nav dropdown
$stmt = $pdo->query("SELECT id, name, slug FROM categories ORDER BY name");
$navCategories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? SITE_NAME) ?></title>
    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- Custom styles -->
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>

<!-- Top bar -->
<div class="topbar py-1 d-none d-md-block">
    <div class="container d-flex justify-content-between align-items-center">
        <small class="text-white opacity-75">
            <i class="bi bi-geo-alt-fill me-1"></i>Proudly South African &nbsp;|&nbsp;
            <span class="zulu-label">Siyakwamukela</span> — Welcome
        </small>
        <small class="text-white opacity-75">
            <i class="bi bi-shield-check me-1"></i>Verified sellers. Trusted trade.
        </small>
    </div>
</div>

<!-- Main navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold fs-4" href="<?= SITE_URL ?>/index.php">
            <i class="bi bi-shop-window me-2"></i>Voetstoots Exchange
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <!-- Search bar -->
            <form class="d-flex mx-auto my-2 my-lg-0" style="width:380px;" action="<?= SITE_URL ?>/listings.php" method="GET">
                <input class="form-control rounded-start" type="search" name="q"
                       placeholder="Khanda / Search listings..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                <button class="btn btn-warning px-3" type="submit"><i class="bi bi-search"></i></button>
            </form>

            <ul class="navbar-nav ms-auto align-items-lg-center gap-1">
                <!-- Browse dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-grid me-1"></i>Browse
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= SITE_URL ?>/listings.php">All Listings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <?php foreach ($navCategories as $cat): ?>
                        <li>
                            <a class="dropdown-item" href="<?= SITE_URL ?>/listings.php?category=<?= $cat['slug'] ?>">
                                <?= htmlspecialchars($cat['name']) ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </li>

                <?php if (isLoggedIn()): ?>
                    <?php if (userRole() === 'seller' || userRole() === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= SITE_URL ?>/dashboard.php">
                            <i class="bi bi-speedometer2 me-1"></i>Dashboard
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= SITE_URL ?>/my-orders.php">
                            <i class="bi bi-bag me-1"></i>My Orders
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($_SESSION['user_name']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= SITE_URL ?>/seller.php?id=<?= userId() ?>">My Profile</a></li>
                            <?php if (userRole() === 'admin'): ?>
                            <li><a class="dropdown-item" href="<?= SITE_URL ?>/admin/index.php">Admin Panel</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= SITE_URL ?>/logout.php">Sign Out</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= SITE_URL ?>/login.php">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-warning btn-sm ms-1" href="<?= SITE_URL ?>/register.php">
                            <i class="bi bi-person-plus me-1"></i>Register
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Flash messages -->
<?php if (!empty($_SESSION['flash'])): ?>
<div class="container mt-3">
    <div class="alert alert-<?= $_SESSION['flash']['type'] ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['flash']['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php unset($_SESSION['flash']); ?>
<?php endif; ?>
