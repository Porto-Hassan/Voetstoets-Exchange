<?php
// ============================================================
// listings.php — Browse all listings with search and filter
// ============================================================
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

// Inputs
$search   = trim($_GET['q']        ?? '');
$catSlug  = trim($_GET['category'] ?? '');
$sort     = $_GET['sort']          ?? 'newest';
$minPrice = isset($_GET['min_price']) ? (float) $_GET['min_price'] : null;
$maxPrice = isset($_GET['max_price']) ? (float) $_GET['max_price'] : null;

// Resolve category
$currentCategory = null;
if ($catSlug) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ?");
    $stmt->execute([$catSlug]);
    $currentCategory = $stmt->fetch();
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Build query
$where  = ["l.status = 'active'"];
$params = [];

if ($search) {
    $where[]  = "(l.title LIKE ? OR l.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($currentCategory) {
    $where[]  = "l.category_id = ?";
    $params[] = $currentCategory['id'];
}
if ($minPrice !== null) {
    $where[]  = "l.price >= ?";
    $params[] = $minPrice;
}
if ($maxPrice !== null) {
    $where[]  = "l.price <= ?";
    $params[] = $maxPrice;
}

$orderBy = match($sort) {
    'price_asc'  => 'l.price ASC',
    'price_desc' => 'l.price DESC',
    'oldest'     => 'l.created_at ASC',
    default      => 'l.created_at DESC',
};

$sql = "
    SELECT l.*, c.name AS category_name, c.slug AS category_slug, u.full_name AS seller_name
    FROM listings l
    JOIN categories c ON l.category_id = c.id
    JOIN users u      ON l.seller_id   = u.id
    WHERE " . implode(' AND ', $where) . "
    ORDER BY $orderBy
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$listings = $stmt->fetchAll();

$pageTitle = ($currentCategory ? $currentCategory['name'] . ' — ' : '') . 'Browse Listings — Voetstoots Exchange';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-4">
    <div class="row g-4">

        <!-- Sidebar filters -->
        <div class="col-md-3">
            <div class="form-card p-3">
                <h6 class="fw-bold mb-3"><i class="bi bi-funnel me-2 text-success"></i>Filter Listings</h6>
                <form method="GET" action="listings.php">
                    <?php if ($search): ?>
                    <input type="hidden" name="q" value="<?= htmlspecialchars($search) ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Category</label>
                        <select name="category" class="form-select form-select-sm auto-submit-select">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['slug'] ?>"
                                <?= $catSlug === $cat['slug'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Price Range (R)</label>
                        <div class="d-flex gap-2">
                            <input type="number" class="form-control form-control-sm" name="min_price"
                                   placeholder="Min" min="0" value="<?= $minPrice ?? '' ?>">
                            <input type="number" class="form-control form-control-sm" name="max_price"
                                   placeholder="Max" min="0" value="<?= $maxPrice ?? '' ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Sort By</label>
                        <select name="sort" class="form-select form-select-sm auto-submit-select">
                            <option value="newest"     <?= $sort === 'newest'     ? 'selected' : '' ?>>Newest First</option>
                            <option value="oldest"     <?= $sort === 'oldest'     ? 'selected' : '' ?>>Oldest First</option>
                            <option value="price_asc"  <?= $sort === 'price_asc'  ? 'selected' : '' ?>>Price: Low to High</option>
                            <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm w-100">Apply Filters</button>
                    <a href="listings.php" class="btn btn-outline-secondary btn-sm w-100 mt-2">Clear All</a>
                </form>
            </div>
        </div>

        <!-- Listings grid -->
        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="fw-bold mb-0">
                        <?= $currentCategory ? htmlspecialchars($currentCategory['name']) : 'All Listings' ?>
                    </h4>
                    <small class="text-muted">
                        <?= count($listings) ?> listing<?= count($listings) !== 1 ? 's' : '' ?> found
                        <?= $search ? ' for "' . htmlspecialchars($search) . '"' : '' ?>
                    </small>
                </div>
            </div>

            <?php if (empty($listings)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-search fs-1 d-block mb-3"></i>
                <p>No listings found. Try adjusting your filters.</p>
                <a href="listings.php" class="btn btn-outline-primary btn-sm">View All Listings</a>
            </div>
            <?php else: ?>
            <div class="row g-3">
                <?php foreach ($listings as $l): ?>
                <div class="col-sm-6 col-lg-4">
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
                                <small class="text-muted d-block mt-1">
                                    <i class="bi bi-person me-1"></i><?= htmlspecialchars($l['seller_name']) ?>
                                </small>
                                <small class="text-muted">
                                    <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($l['location'] ?? 'South Africa') ?>
                                </small>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
