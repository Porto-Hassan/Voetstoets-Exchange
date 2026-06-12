<?php
// ============================================================
// admin/index.php — Admin Dashboard Overview
// ============================================================
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/admin-header.php';

// Stats
$totalUsers    = $pdo->query("SELECT COUNT(*) FROM users WHERE role != 'admin'")->fetchColumn();
$totalSellers  = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'seller'")->fetchColumn();
$totalListings = $pdo->query("SELECT COUNT(*) FROM listings")->fetchColumn();
$pendingListings = $pdo->query("SELECT COUNT(*) FROM listings WHERE status = 'pending'")->fetchColumn();
$totalOrders   = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalRevenue  = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE payment_status = 'paid'")->fetchColumn();

// Recent listings pending approval
$pending = $pdo->query("
    SELECT l.*, u.full_name AS seller_name, c.name AS category_name
    FROM listings l
    JOIN users u      ON l.seller_id   = u.id
    JOIN categories c ON l.category_id = c.id
    WHERE l.status = 'pending'
    ORDER BY l.created_at DESC
    LIMIT 5
")->fetchAll();

// Recent orders
$recentOrders = $pdo->query("
    SELECT o.*, u.full_name AS buyer_name, l.title AS listing_title
    FROM orders o
    JOIN users u        ON o.buyer_id    = u.id
    JOIN order_items oi ON oi.order_id   = o.id
    JOIN listings l     ON oi.listing_id = l.id
    ORDER BY o.created_at DESC
    LIMIT 5
")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-0">Dashboard</h2>
        <p class="text-muted small mb-0">Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?></p>
    </div>
    <span class="badge bg-success fs-6">Admin</span>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card">
            <div class="stat-value"><?= $totalUsers ?></div>
            <div class="stat-label">Total Users</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card">
            <div class="stat-value"><?= $totalSellers ?></div>
            <div class="stat-label">Sellers</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card gold">
            <div class="stat-value"><?= $pendingListings ?></div>
            <div class="stat-label">Pending Listings</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card">
            <div class="stat-value"><?= $totalListings ?></div>
            <div class="stat-label">Total Listings</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card blue">
            <div class="stat-value"><?= $totalOrders ?></div>
            <div class="stat-label">Orders</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card">
            <div class="stat-value" style="font-size:1.3rem;">R<?= number_format($totalRevenue, 0) ?></div>
            <div class="stat-label">Revenue</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Pending approvals -->
    <div class="col-lg-7">
        <div class="bg-white rounded-3 shadow-sm p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-clock-history me-2 text-warning"></i>Pending Approvals
                </h5>
                <a href="<?= SITE_URL ?>/admin/listings.php?status=pending" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <?php if (empty($pending)): ?>
            <p class="text-muted small mb-0">No listings awaiting approval.</p>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Seller</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending as $l): ?>
                        <tr>
                            <td class="small fw-semibold"><?= htmlspecialchars($l['title']) ?></td>
                            <td class="small"><?= htmlspecialchars($l['seller_name']) ?></td>
                            <td class="small"><?= htmlspecialchars($l['category_name']) ?></td>
                            <td class="small">R <?= number_format($l['price'], 2) ?></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <form method="POST" action="listings.php">
                                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                        <input type="hidden" name="listing_id" value="<?= $l['id'] ?>">
                                        <input type="hidden" name="action"     value="approve">
                                        <button class="btn btn-sm btn-success" title="Approve">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="listings.php">
                                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                        <input type="hidden" name="listing_id" value="<?= $l['id'] ?>">
                                        <input type="hidden" name="action"     value="remove">
                                        <button class="btn btn-sm btn-danger" title="Reject">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent orders -->
    <div class="col-lg-5">
        <div class="bg-white rounded-3 shadow-sm p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">
                    <i class="bi bi-bag me-2 text-success"></i>Recent Orders
                </h5>
                <a href="<?= SITE_URL ?>/admin/orders.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <?php if (empty($recentOrders)): ?>
            <p class="text-muted small mb-0">No orders placed yet.</p>
            <?php else: ?>
            <?php foreach ($recentOrders as $o): ?>
            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                <div>
                    <div class="small fw-semibold"><?= htmlspecialchars($o['buyer_name']) ?></div>
                    <div class="small text-muted"><?= htmlspecialchars(mb_strimwidth($o['listing_title'], 0, 30, '...')) ?></div>
                </div>
                <div class="text-end">
                    <div class="small fw-semibold text-success">R <?= number_format($o['total_amount'], 2) ?></div>
                    <span class="status-badge status-<?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
