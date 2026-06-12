<?php
// ============================================================
// admin/listings.php — Manage All Listings (Approve / Remove)
// ============================================================
$pageTitle = 'Manage Listings';
require_once __DIR__ . '/includes/admin-header.php';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCsrf($_POST['csrf_token'] ?? '')) {
    $lid    = (int) ($_POST['listing_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    $newStatus = match($action) {
        'approve' => 'active',
        'remove'  => 'removed',
        'pending' => 'pending',
        default   => null,
    };

    if ($newStatus && $lid) {
        $pdo->prepare("UPDATE listings SET status = ? WHERE id = ?")->execute([$newStatus, $lid]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Listing status updated.'];
    }

    header('Location: ' . SITE_URL . '/admin/listings.php' . ($_GET['status'] ? '?status=' . $_GET['status'] : ''));
    exit;
}

// Filters
$statusFilter = $_GET['status'] ?? '';
$searchFilter = trim($_GET['q'] ?? '');

$where  = ['1=1'];
$params = [];

if ($statusFilter && in_array($statusFilter, ['active', 'pending', 'sold', 'removed'])) {
    $where[]  = "l.status = ?";
    $params[] = $statusFilter;
}
if ($searchFilter) {
    $where[]  = "(l.title LIKE ? OR u.full_name LIKE ?)";
    $params[] = "%$searchFilter%";
    $params[] = "%$searchFilter%";
}

$sql = "
    SELECT l.*, c.name AS category_name, u.full_name AS seller_name
    FROM listings l
    JOIN categories c ON l.category_id = c.id
    JOIN users u      ON l.seller_id   = u.id
    WHERE " . implode(' AND ', $where) . "
    ORDER BY l.created_at DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$listings = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Manage Listings</h2>
    <span class="text-muted small"><?= count($listings) ?> listing(s) found</span>
</div>

<!-- Filter tabs -->
<div class="d-flex gap-2 mb-3 flex-wrap">
    <?php
    $tabs = ['' => 'All', 'pending' => 'Pending', 'active' => 'Active', 'removed' => 'Removed', 'sold' => 'Sold'];
    foreach ($tabs as $val => $label):
    ?>
    <a href="listings.php?status=<?= $val ?><?= $searchFilter ? '&q=' . urlencode($searchFilter) : '' ?>"
       class="btn btn-sm <?= $statusFilter === $val ? 'btn-primary' : 'btn-outline-secondary' ?>">
        <?= $label ?>
    </a>
    <?php endforeach; ?>
</div>

<!-- Search -->
<div class="bg-white rounded-3 shadow-sm p-3 mb-4">
    <form method="GET" class="d-flex gap-2">
        <?php if ($statusFilter): ?>
        <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">
        <?php endif; ?>
        <input type="text" class="form-control form-control-sm" name="q"
               placeholder="Search by title or seller..." value="<?= htmlspecialchars($searchFilter) ?>" style="max-width:300px;">
        <button class="btn btn-primary btn-sm">Search</button>
        <a href="listings.php" class="btn btn-outline-secondary btn-sm">Clear</a>
    </form>
</div>

<div class="bg-white rounded-3 shadow-sm p-4">
    <?php if (empty($listings)): ?>
    <p class="text-muted text-center py-3">No listings found.</p>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Listing</th>
                    <th>Seller</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($listings as $l): ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img src="<?= $l['image']
                                ? SITE_URL . '/uploads/listings/' . htmlspecialchars($l['image'])
                                : SITE_URL . '/assets/img/placeholder.jpg' ?>"
                                 style="width:44px;height:44px;object-fit:cover;border-radius:6px;" alt="">
                            <span class="small fw-semibold"><?= htmlspecialchars($l['title']) ?></span>
                        </div>
                    </td>
                    <td class="small"><?= htmlspecialchars($l['seller_name']) ?></td>
                    <td class="small"><?= htmlspecialchars($l['category_name']) ?></td>
                    <td class="small fw-semibold">R <?= number_format($l['price'], 2) ?></td>
                    <td>
                        <span class="status-badge status-<?= $l['status'] ?>">
                            <?= ucfirst($l['status']) ?>
                        </span>
                    </td>
                    <td class="small text-muted"><?= date('d M Y', strtotime($l['created_at'])) ?></td>
                    <td>
                        <div class="d-flex gap-1">
                            <?php if ($l['status'] === 'pending'): ?>
                            <form method="POST">
                                <input type="hidden" name="csrf_token"  value="<?= csrfToken() ?>">
                                <input type="hidden" name="listing_id" value="<?= $l['id'] ?>">
                                <input type="hidden" name="action"     value="approve">
                                <button class="btn btn-sm btn-success" title="Approve">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                            <?php if ($l['status'] === 'active'): ?>
                            <a href="<?= SITE_URL ?>/listing.php?id=<?= $l['id'] ?>"
                               class="btn btn-sm btn-outline-secondary" target="_blank" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            <?php endif; ?>
                            <?php if ($l['status'] !== 'removed'): ?>
                            <form method="POST" class="confirm-delete" data-confirm="Remove this listing from the platform?">
                                <input type="hidden" name="csrf_token"  value="<?= csrfToken() ?>">
                                <input type="hidden" name="listing_id" value="<?= $l['id'] ?>">
                                <input type="hidden" name="action"     value="remove">
                                <button class="btn btn-sm btn-outline-danger" title="Remove">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
