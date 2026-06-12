<?php
// ============================================================
// dashboard.php — Seller Dashboard
// ============================================================
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
requireSeller();

$uid = userId();

// Stats
$totalListings = $pdo->prepare("SELECT COUNT(*) FROM listings WHERE seller_id = ?");
$totalListings->execute([$uid]); $totalListings = $totalListings->fetchColumn();

$activeListings = $pdo->prepare("SELECT COUNT(*) FROM listings WHERE seller_id = ? AND status = 'active'");
$activeListings->execute([$uid]); $activeListings = $activeListings->fetchColumn();

$pendingListings = $pdo->prepare("SELECT COUNT(*) FROM listings WHERE seller_id = ? AND status = 'pending'");
$pendingListings->execute([$uid]); $pendingListings = $pendingListings->fetchColumn();

$totalRevenue = $pdo->prepare("
    SELECT COALESCE(SUM(oi.unit_price * oi.quantity), 0)
    FROM order_items oi
    JOIN listings l ON oi.listing_id = l.id
    JOIN orders o   ON oi.order_id   = o.id
    WHERE l.seller_id = ? AND o.payment_status = 'paid'
");
$totalRevenue->execute([$uid]); $totalRevenue = $totalRevenue->fetchColumn();

// My listings
$stmt = $pdo->prepare("
    SELECT l.*, c.name AS category_name
    FROM listings l
    JOIN categories c ON l.category_id = c.id
    WHERE l.seller_id = ?
    ORDER BY l.created_at DESC
");
$stmt->execute([$uid]);
$myListings = $stmt->fetchAll();

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_listing'])) {
    if (validateCsrf($_POST['csrf_token'] ?? '')) {
        $delId = (int) $_POST['delete_listing'];
        $check = $pdo->prepare("SELECT id FROM listings WHERE id = ? AND seller_id = ?");
        $check->execute([$delId, $uid]);
        if ($check->fetch()) {
            $pdo->prepare("UPDATE listings SET status = 'removed' WHERE id = ?")->execute([$delId]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Listing removed successfully.'];
        }
        header('Location: ' . SITE_URL . '/dashboard.php');
        exit;
    }
}

$pageTitle = 'Seller Dashboard — Voetstoots Exchange';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="fw-bold mb-0">Seller Dashboard</h2>
            <p class="text-muted mb-0 small">
                <span class="zulu-label">Ukuphatha amakhono akho</span> — Manage your listings
            </p>
        </div>
        <a href="add-listing.php" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>New Listing
        </a>
    </div>

    <!-- Stats row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-value"><?= $totalListings ?></div>
                <div class="stat-label">Total Listings</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-value text-success"><?= $activeListings ?></div>
                <div class="stat-label">Active</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card gold">
                <div class="stat-value"><?= $pendingListings ?></div>
                <div class="stat-label">Pending Approval</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card blue">
                <div class="stat-value">R <?= number_format($totalRevenue, 2) ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
        </div>
    </div>

    <!-- Listings table -->
    <div class="bg-white rounded-3 shadow-sm p-4">
        <h5 class="fw-bold mb-3">My Listings</h5>
        <?php if (empty($myListings)): ?>
        <div class="text-center py-4 text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-3"></i>
            <p>You have no listings yet.</p>
            <a href="add-listing.php" class="btn btn-primary btn-sm">Post Your First Listing</a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Listing</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($myListings as $l): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="<?= $l['image']
                                    ? SITE_URL . '/uploads/listings/' . htmlspecialchars($l['image'])
                                    : SITE_URL . '/assets/img/placeholder.jpg' ?>"
                                     style="width:48px;height:48px;object-fit:cover;border-radius:8px;" alt="">
                                <span class="fw-semibold small"><?= htmlspecialchars($l['title']) ?></span>
                            </div>
                        </td>
                        <td><small><?= htmlspecialchars($l['category_name']) ?></small></td>
                        <td><small class="fw-semibold">R <?= number_format($l['price'], 2) ?></small></td>
                        <td>
                            <span class="status-badge status-<?= $l['status'] ?>">
                                <?= ucfirst($l['status']) ?>
                            </span>
                        </td>
                        <td><small class="text-muted"><?= date('d M Y', strtotime($l['created_at'])) ?></small></td>
                        <td>
                            <div class="d-flex gap-1">
                                <?php if ($l['status'] === 'active'): ?>
                                <a href="listing.php?id=<?= $l['id'] ?>" class="btn btn-sm btn-outline-secondary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php endif; ?>
                                <a href="edit-listing.php?id=<?= $l['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" class="confirm-delete" data-confirm="Remove this listing?">
                                    <input type="hidden" name="csrf_token"     value="<?= csrfToken() ?>">
                                    <input type="hidden" name="delete_listing" value="<?= $l['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove">
                                        <i class="bi bi-trash"></i>
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
