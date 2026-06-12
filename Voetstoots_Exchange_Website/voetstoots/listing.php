<?php
// ============================================================
// listing.php — Single Listing Detail Page
// ============================================================
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $pdo->prepare("
    SELECT l.*, c.name AS category_name, c.slug AS category_slug,
           u.id AS seller_id, u.full_name AS seller_name,
           u.location AS seller_location, u.bio AS seller_bio,
           u.profile_img AS seller_img, u.created_at AS seller_joined
    FROM listings l
    JOIN categories c ON l.category_id = c.id
    JOIN users u      ON l.seller_id   = u.id
    WHERE l.id = ? AND l.status = 'active'
");
$stmt->execute([$id]);
$listing = $stmt->fetch();

if (!$listing) {
    $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Listing not found or no longer available.'];
    header('Location: ' . SITE_URL . '/listings.php');
    exit;
}

// Seller listing count
$stmt2 = $pdo->prepare("SELECT COUNT(*) FROM listings WHERE seller_id = ? AND status = 'active'");
$stmt2->execute([$listing['seller_id']]);
$sellerListingCount = $stmt2->fetchColumn();

// Reviews
$stmt3 = $pdo->prepare("
    SELECT r.*, u.full_name AS reviewer_name
    FROM reviews r
    JOIN users u ON r.buyer_id = u.id
    WHERE r.listing_id = ?
    ORDER BY r.created_at DESC
");
$stmt3->execute([$id]);
$reviews = $stmt3->fetchAll();
$avgRating = count($reviews) ? array_sum(array_column($reviews, 'rating')) / count($reviews) : null;

// Related listings
$stmt4 = $pdo->prepare("
    SELECT l.*, c.name AS category_name
    FROM listings l
    JOIN categories c ON l.category_id = c.id
    WHERE l.category_id = ? AND l.id != ? AND l.status = 'active'
    LIMIT 4
");
$stmt4->execute([$listing['category_id'] ?? 0, $id]);
$related = $stmt4->fetchAll();

$pageTitle = htmlspecialchars($listing['title']) . ' — Voetstoots Exchange';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="listings.php">Listings</a></li>
            <li class="breadcrumb-item">
                <a href="listings.php?category=<?= $listing['category_slug'] ?>">
                    <?= htmlspecialchars($listing['category_name']) ?>
                </a>
            </li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($listing['title']) ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Image + details -->
        <div class="col-md-7">
            <div class="bg-white rounded-3 overflow-hidden shadow-sm mb-3">
                <img src="<?= $listing['image']
                    ? SITE_URL . '/uploads/listings/' . htmlspecialchars($listing['image'])
                    : SITE_URL . '/assets/img/placeholder.jpg' ?>"
                     class="w-100" style="max-height:420px;object-fit:cover;"
                     alt="<?= htmlspecialchars($listing['title']) ?>">
            </div>

            <!-- Reviews -->
            <div class="bg-white rounded-3 shadow-sm p-4">
                <h5 class="fw-bold mb-3">
                    Reviews
                    <?php if ($avgRating): ?>
                    <span class="text-warning ms-2">
                        <?= str_repeat('★', round($avgRating)) ?><?= str_repeat('☆', 5 - round($avgRating)) ?>
                    </span>
                    <small class="text-muted fs-6">(<?= number_format($avgRating, 1) ?> / 5)</small>
                    <?php endif; ?>
                </h5>
                <?php if (empty($reviews)): ?>
                <p class="text-muted small">No reviews yet. Be the first buyer!</p>
                <?php else: ?>
                <?php foreach ($reviews as $r): ?>
                <div class="border-bottom pb-3 mb-3">
                    <div class="d-flex justify-content-between">
                        <strong class="small"><?= htmlspecialchars($r['reviewer_name']) ?></strong>
                        <span class="text-warning small">
                            <?= str_repeat('★', $r['rating']) ?><?= str_repeat('☆', 5 - $r['rating']) ?>
                        </span>
                    </div>
                    <p class="small text-muted mb-0"><?= htmlspecialchars($r['comment']) ?></p>
                    <small class="text-muted"><?= date('d M Y', strtotime($r['created_at'])) ?></small>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-5">
            <!-- Listing info -->
            <div class="bg-white rounded-3 shadow-sm p-4 mb-3">
                <span class="badge-category d-inline-block mb-2">
                    <?= htmlspecialchars($listing['category_name']) ?>
                </span>
                <h2 class="fw-bold fs-4 mb-2"><?= htmlspecialchars($listing['title']) ?></h2>
                <div class="price fs-2 mb-3">R <?= number_format($listing['price'], 2) ?></div>

                <p class="text-muted mb-3"><?= nl2br(htmlspecialchars($listing['description'])) ?></p>

                <ul class="list-unstyled small text-muted mb-4">
                    <li><i class="bi bi-box-seam me-2 text-success"></i>
                        Available qty: <strong><?= (int) $listing['quantity'] ?></strong></li>
                    <li><i class="bi bi-geo-alt me-2 text-success"></i>
                        <?= htmlspecialchars($listing['location'] ?? 'South Africa') ?></li>
                    <li><i class="bi bi-calendar me-2 text-success"></i>
                        Listed on <?= date('d M Y', strtotime($listing['created_at'])) ?></li>
                </ul>

                <?php if (isLoggedIn() && userId() !== $listing['seller_id']): ?>
                <a href="checkout.php?listing_id=<?= $listing['id'] ?>" class="btn btn-primary w-100 py-2 fw-semibold mb-2">
                    <i class="bi bi-bag-check me-2"></i>Buy Now
                </a>
                <?php elseif (!isLoggedIn()): ?>
                <a href="login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>"
                   class="btn btn-primary w-100 py-2 fw-semibold mb-2">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Login to Buy
                </a>
                <?php elseif (userId() === $listing['seller_id']): ?>
                <a href="edit-listing.php?id=<?= $listing['id'] ?>" class="btn btn-outline-primary w-100 mb-2">
                    <i class="bi bi-pencil me-2"></i>Edit This Listing
                </a>
                <?php endif; ?>
            </div>

            <!-- Seller card -->
            <div class="bg-white rounded-3 shadow-sm p-4">
                <h6 class="fw-bold mb-3">About the Seller</h6>
                <div class="d-flex align-items-center gap-3 mb-3">
                    <?php if ($listing['seller_img']): ?>
                    <img src="<?= SITE_URL . '/uploads/listings/' . htmlspecialchars($listing['seller_img']) ?>"
                         class="seller-avatar" alt="">
                    <?php else: ?>
                    <div class="seller-avatar bg-success d-flex align-items-center justify-content-center text-white fs-3">
                        <?= strtoupper(substr($listing['seller_name'], 0, 1)) ?>
                    </div>
                    <?php endif; ?>
                    <div>
                        <a href="seller.php?id=<?= $listing['seller_id'] ?>" class="fw-semibold text-decoration-none">
                            <?= htmlspecialchars($listing['seller_name']) ?>
                        </a>
                        <br>
                        <span class="seller-badge">Verified Seller</span>
                    </div>
                </div>
                <ul class="list-unstyled small text-muted">
                    <li><i class="bi bi-geo-alt me-2 text-success"></i>
                        <?= htmlspecialchars($listing['seller_location'] ?? 'South Africa') ?></li>
                    <li><i class="bi bi-grid me-2 text-success"></i>
                        <?= $sellerListingCount ?> active listing<?= $sellerListingCount !== 1 ? 's' : '' ?></li>
                    <li><i class="bi bi-calendar me-2 text-success"></i>
                        Member since <?= date('M Y', strtotime($listing['seller_joined'])) ?></li>
                </ul>
                <a href="seller.php?id=<?= $listing['seller_id'] ?>" class="btn btn-outline-primary btn-sm w-100 mt-2">
                    View All Listings by This Seller
                </a>
            </div>
        </div>
    </div>

    <!-- Related listings -->
    <?php if (!empty($related)): ?>
    <section class="mt-5">
        <h4 class="section-heading">More in <?= htmlspecialchars($listing['category_name']) ?></h4>
        <div class="row g-3">
            <?php foreach ($related as $r): ?>
            <div class="col-sm-6 col-md-3">
                <a href="listing.php?id=<?= $r['id'] ?>" class="text-decoration-none text-dark">
                    <div class="listing-card">
                        <img src="<?= $r['image']
                            ? SITE_URL . '/uploads/listings/' . htmlspecialchars($r['image'])
                            : SITE_URL . '/assets/img/placeholder.jpg' ?>"
                             alt="<?= htmlspecialchars($r['title']) ?>">
                        <div class="card-body">
                            <h6 class="fw-semibold small lh-sm mb-1"><?= htmlspecialchars($r['title']) ?></h6>
                            <div class="price">R <?= number_format($r['price'], 2) ?></div>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
