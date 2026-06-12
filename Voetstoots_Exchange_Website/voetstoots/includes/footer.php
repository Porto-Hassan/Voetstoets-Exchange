<?php
// ============================================================
// includes/footer.php — Shared site footer
// ============================================================
?>
<footer class="footer mt-5 pt-5 pb-3 bg-dark text-white">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <h5 class="fw-bold mb-3">
                    <i class="bi bi-shop-window me-2"></i>Voetstoots Exchange
                </h5>
                <p class="text-white-50 small">
                    Buy local. Sell local. Keep it real.<br>
                    <span class="zulu-label">Thenga endaweni. Thengisa endaweni.</span>
                </p>
                <p class="text-white-50 small">
                    A C2C marketplace built for South Africa's informal economy —
                    connecting township traders with buyers across the country.
                </p>
            </div>
            <div class="col-md-2">
                <h6 class="fw-semibold mb-3 text-warning">Browse</h6>
                <ul class="list-unstyled small">
                    <li><a class="text-white-50 text-decoration-none" href="<?= SITE_URL ?>/listings.php">All Listings</a></li>
                    <li><a class="text-white-50 text-decoration-none" href="<?= SITE_URL ?>/listings.php?category=fresh-produce">Fresh Produce</a></li>
                    <li><a class="text-white-50 text-decoration-none" href="<?= SITE_URL ?>/listings.php?category=homemade-food">Homemade Food</a></li>
                    <li><a class="text-white-50 text-decoration-none" href="<?= SITE_URL ?>/listings.php?category=crafts-art">Crafts & Art</a></li>
                    <li><a class="text-white-50 text-decoration-none" href="<?= SITE_URL ?>/listings.php?category=clothing-textiles">Clothing & Textiles</a></li>
                    <li><a class="text-white-50 text-decoration-none" href="<?= SITE_URL ?>/listings.php?category=farming-supplies">Farming Supplies</a></li>
                </ul>
            </div>
            <div class="col-md-2">
                <h6 class="fw-semibold mb-3 text-warning">Account</h6>
                <ul class="list-unstyled small">
                    <li><a class="text-white-50 text-decoration-none" href="<?= SITE_URL ?>/register.php">Register</a></li>
                    <li><a class="text-white-50 text-decoration-none" href="<?= SITE_URL ?>/login.php">Login</a></li>
                    <li><a class="text-white-50 text-decoration-none" href="<?= SITE_URL ?>/dashboard.php">Seller Dashboard</a></li>
                    <li><a class="text-white-50 text-decoration-none" href="<?= SITE_URL ?>/my-orders.php">My Orders</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h6 class="fw-semibold mb-3 text-warning">About the Platform</h6>
                <p class="text-white-50 small">
                    Voetstoots Exchange was built as part of an academic project at Eduvos
                    for the Web Development and E-Commerce module (ITECA3-12). It is a
                    demonstration platform and is not a registered commercial entity.
                </p>
                <p class="text-white-50 small mb-0">
                    <i class="bi bi-geo-alt-fill me-1 text-warning"></i>Vereeniging, Gauteng, South Africa
                </p>
            </div>
        </div>
        <hr class="border-secondary mt-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center small text-white-50">
            <span>&copy; <?= date('Y') ?> Voetstoots Exchange. Academic project — Eduvos.</span>
            <span class="mt-2 mt-md-0">Built with HTML &bull; CSS &bull; JavaScript &bull; PHP &bull; MySQL</span>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
</body>
</html>
