<?php
// ============================================================
// admin/includes/admin-header.php
// ============================================================
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — <?= htmlspecialchars($pageTitle ?? 'Voetstoots Exchange') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>

<div class="admin-sidebar">
    <div class="brand">
        <i class="bi bi-shield-lock me-2"></i>VE Admin
    </div>
    <nav class="mt-2">
        <a href="<?= SITE_URL ?>/admin/index.php"
           class="nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>">
            <i class="bi bi-speedometer2 me-2"></i>Dashboard
        </a>
        <a href="<?= SITE_URL ?>/admin/users.php"
           class="nav-link <?= $currentPage === 'users.php' ? 'active' : '' ?>">
            <i class="bi bi-people me-2"></i>Users
        </a>
        <a href="<?= SITE_URL ?>/admin/listings.php"
           class="nav-link <?= $currentPage === 'listings.php' ? 'active' : '' ?>">
            <i class="bi bi-grid me-2"></i>Listings
        </a>
        <a href="<?= SITE_URL ?>/admin/orders.php"
           class="nav-link <?= $currentPage === 'orders.php' ? 'active' : '' ?>">
            <i class="bi bi-bag me-2"></i>Orders
        </a>
        <hr style="border-color:rgba(255,255,255,0.1);margin:0.5rem 1rem;">
        <a href="<?= SITE_URL ?>/index.php" class="nav-link">
            <i class="bi bi-box-arrow-left me-2"></i>Back to Site
        </a>
        <a href="<?= SITE_URL ?>/logout.php" class="nav-link text-danger">
            <i class="bi bi-power me-2"></i>Sign Out
        </a>
    </nav>
</div>

<div class="admin-content">
<!-- Flash messages -->
<?php if (!empty($_SESSION['flash'])): ?>
<div class="alert alert-<?= $_SESSION['flash']['type'] ?> alert-dismissible fade show mb-4" role="alert">
    <?= htmlspecialchars($_SESSION['flash']['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['flash']); ?>
<?php endif; ?>
