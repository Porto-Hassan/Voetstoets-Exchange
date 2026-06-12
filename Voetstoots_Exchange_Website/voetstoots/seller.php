<?php
// ============================================================
// seller.php — Public Seller Profile Page
// ============================================================
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

$sellerId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role IN ('seller','admin') AND is_active = 1");
$stmt->execute([$sellerId]);
$seller = $stmt->fetch();

if (!$seller) {
    $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Seller profile not found.'];
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

$stmt2 = $pdo->prepare("
    SELECT l.*, c.name AS category_name, c.slug AS category_slug
    FROM listings l
    JOIN categories c ON l.category_id = c.id
    WHERE l.seller_id = ? AND l.status = 'active'
    ORDER BY l.created_at DESC
");
$stmt2->execute([$sellerId]);
$sellerListings = $stmt2->fetchAll();

$pageTitle = htmlspecialchars($seller['full_name']) . ' — Seller Profile — Voetstoots Exchange';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-4" style="max-width:900px;">
    <!-- Seller header -->
    <div class="bg-white rounded-3 shadow-sm p-4 mb-4 d-flex align-items-center gap-4 flex-wrap">
        <div class="seller-avatar bg-success d-flex align-items-center justify-content-center text-white"
             style="width:90px;height:90px;border-radius:50%;font-size:2.5rem;">
            <?php if ($seller['profile_img']): ?>
            <img src="<?= SITE_URL . '/uploads/' . htmlspecialchars($seller['profile_img']) ?>"
                 class="seller-avatar" alt="">
            <?php else: ?>
            <?= strtoupper(substr($seller['full_name'], 0, 1)) ?>
            <?php endif; ?>
        </div>
        <div class="flex-grow-1">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h2 class="fw-bold mb-0"><?= htmlspecialchars($seller['full_name']) ?></h2>
                <span class="seller-badge">Verified Seller</span>
            </div>
            <?php if ($seller['location']): ?>
            <p class="text-muted small mb-1">
                <i class="bi bi-geo-alt me-1 text-success"></i><?= htmlspecialchars($seller['location']) ?>
            </p>
            <?php endif; ?>
            <?php if ($seller['bio']): ?>
            <p class="mb-0 small"><?= nl2br(htmlspecialchars($seller['bio'])) ?></p>
            <?php endif; ?>
        </div>
        <div class="text-center">
            <div class="stat-value fs-2 fw-bold text-success"><?= count($sellerListings) ?></div>
            <div class="text-muted small">Active Listings</div>
        </div>
    </div>

    <!-- Seller listings -->
    <h4 class="section-heading">Listings by <?= htmlspecialchars($seller['full_name']) ?></h4>

    <?php if (empty($sellerListings)): ?>
    <div class="text-center py-5 text-muted">
        <i class="bi bi-inbox fs-1 d-block mb-3"></i>
        <p>This seller has no active listings at the moment.</p>
    </div>
    <?php else: ?>
    <div class="row g-3">
        <?php foreach ($sellerListings as $l): ?>
        <div class="col-sm-6 col-md-4">
            <a href="listing.php?id=<?= $l['id'] ?>" class="text-decoration-none text-dark">
                <div class="listing-card">
                    <img src="<?= $l['image']
                        ? SITE_URL . '/uploads/listings/' . htmlspecialchars($l['image'])
                        : SITE_URL . '/assets/img/placeholder.jpg' ?>"
                         alt="<?= htmlspecialchars($l['title']) ?>">
                    <div class="card-body">
                        <span class="badge-category mb-2 d-inline-block">
                            <?= htmlspecialchars($l['category_name']) ?>
                        </span>
                        <h6 class="fw-semibold lh-sm mb-1"><?= htmlspecialchars($l['title']) ?></h6>
                        <div class="price">R <?= number_format($l['price'], 2) ?></div>
                    </div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
