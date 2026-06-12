<?php
// ============================================================
// index.php — Voetstoots Exchange Homepage
// ============================================================
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Voetstoots Exchange — Buy local. Sell local. Keep it real.';

// Fetch categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Fetch 8 most recent active listings
$stmt = $pdo->query("
    SELECT l.*, c.name AS category_name, c.slug AS category_slug,
           u.full_name AS seller_name
    FROM listings l
    JOIN categories c ON l.category_id = c.id
    JOIN users u      ON l.seller_id   = u.id
    WHERE l.status = 'active'
    ORDER BY l.created_at DESC
    LIMIT 8
");
$featuredListings = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="hero">
    <div class="container text-center">
        <h1 class="mb-3">Buy local. Sell local. Keep it real.</h1>
        <p class="tagline mb-4">
            <span class="zulu-label">Thenga endaweni. Thengisa endaweni.</span><br>
            South Africa's C2C marketplace for informal traders, home producers and township entrepreneurs.
        </p>
        <form class="d-flex justify-content-center gap-2 flex-wrap" action="listings.php" method="GET">
            <input class="form-control form-control-lg" style="max-width:420px;" type="search"
                   name="q" placeholder="Search for produce, crafts, food...">
            <button class="btn btn-warning btn-lg px-4" type="submit">
                <i class="bi bi-search me-2"></i>Search
            </button>
        </form>
        <div class="mt-4 d-flex justify-content-center gap-3 flex-wrap">
            <a href="listings.php" class="btn btn-outline-light btn-sm">Browse All Listings</a>
            <?php if (!isLoggedIn()): ?>
            <a href="register.php" class="btn btn-warning btn-sm">Start Selling Today</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Categories -->
<section class="container my-5">
    <h2 class="section-heading">Browse by Category</h2>
    <div class="row g-3">
        <?php foreach ($categories as $cat): ?>
        <div class="col-6 col-md-4 col-lg-2-4">
            <a href="listings.php?category=<?= $cat['slug'] ?>" class="category-card">
                <div class="cat-icon"><i class="bi <?= htmlspecialchars($cat['icon']) ?>"></i></div>
                <div class="fw-semibold small"><?= htmlspecialchars($cat['name']) ?></div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Featured Listings -->
<section class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="section-heading mb-0">Latest Listings</h2>
        <a href="listings.php" class="btn btn-outline-primary btn-sm">View All</a>
    </div>

    <?php if (empty($featuredListings)): ?>
    <div class="text-center py-5 text-muted">
        <i class="bi bi-inbox fs-1 d-block mb-3"></i>
        <p>No listings yet. Be the first to post!</p>
        <a href="register.php" class="btn btn-primary">Create an Account</a>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <?php foreach ($featuredListings as $listing): ?>
        <div class="col-sm-6 col-md-4 col-lg-3">
            <a href="listing.php?id=<?= $listing['id'] ?>" class="text-decoration-none text-dark">
                <div class="listing-card">
                    <img src="<?= $listing['image']
                        ? SITE_URL . '/uploads/listings/' . htmlspecialchars($listing['image'])
                        : SITE_URL . '/assets/img/placeholder.jpg' ?>"
                         alt="<?= htmlspecialchars($listing['title']) ?>">
                    <div class="card-body">
                        <span class="badge-category mb-2 d-inline-block">
                            <?= htmlspecialchars($listing['category_name']) ?>
                        </span>
                        <h6 class="fw-semibold mb-1 lh-sm"><?= htmlspecialchars($listing['title']) ?></h6>
                        <div class="price mb-1">R <?= number_format($listing['price'], 2) ?></div>
                        <small class="text-muted">
                            <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($listing['location'] ?? 'South Africa') ?>
                        </small>
                    </div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<!-- Why Voetstoots Exchange -->
<section class="bg-white py-5 mt-4">
    <div class="container">
        <h2 class="section-heading text-center mx-auto" style="width:fit-content;">Why Voetstoots Exchange?</h2>
        <div class="row g-4 mt-2 text-center">
            <div class="col-md-4">
                <div class="p-4">
                    <i class="bi bi-shield-check fs-1 text-success mb-3 d-block"></i>
                    <h5 class="fw-bold">Verified Sellers</h5>
                    <p class="text-muted small">Every seller goes through a registration and verification process before listing. Buyers can trade with confidence.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4">
                    <i class="bi bi-phone fs-1 text-success mb-3 d-block"></i>
                    <h5 class="fw-bold">Low-Data Friendly</h5>
                    <p class="text-muted small">Built for South African data realities. The platform is optimised for mobile and designed to use as little data as possible.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4">
                    <i class="bi bi-people fs-1 text-success mb-3 d-block"></i>
                    <h5 class="fw-bold">Built for Townships</h5>
                    <p class="text-muted small">Voetstoots Exchange exists for the informal economy. From Vereeniging to Soweto, your community is the market.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<?php if (!isLoggedIn()): ?>
<section class="hero py-5 mt-0">
    <div class="container text-center">
        <h3 class="fw-bold mb-3">Ready to start selling?</h3>
        <p class="opacity-75 mb-4">
            <span class="zulu-label">Lungele ukuthengisa</span> — Get ready to sell.<br>
            Create your free account and post your first listing in minutes.
        </p>
        <a href="register.php" class="btn btn-warning btn-lg px-5">Create Free Account</a>
    </div>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
