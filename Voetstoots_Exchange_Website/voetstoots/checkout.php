<?php
// ============================================================
// checkout.php — Checkout with Simulated Payment
// ============================================================
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$listingId = isset($_GET['listing_id']) ? (int) $_GET['listing_id'] : 0;

$stmt = $pdo->prepare("
    SELECT l.*, u.full_name AS seller_name, c.name AS category_name
    FROM listings l
    JOIN users u      ON l.seller_id   = u.id
    JOIN categories c ON l.category_id = c.id
    WHERE l.id = ? AND l.status = 'active'
");
$stmt->execute([$listingId]);
$listing = $stmt->fetch();

if (!$listing || $listing['seller_id'] === userId()) {
    $_SESSION['flash'] = ['type' => 'warning', 'message' => 'This listing is not available for purchase.'];
    header('Location: ' . SITE_URL . '/listings.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid form submission.';
    } else {
        $qty      = max(1, (int) ($_POST['quantity'] ?? 1));
        $address  = trim($_POST['delivery_address'] ?? '');
        $method   = in_array($_POST['payment_method'] ?? '', ['simulated']) ? 'simulated' : 'simulated';

        if (empty($address)) $errors[] = 'Please provide a delivery address.';
        if ($qty > $listing['quantity']) $errors[] = 'Requested quantity exceeds available stock.';

        if (empty($errors)) {
            $total     = $listing['price'] * $qty;
            $reference = 'VE-' . strtoupper(uniqid());

            // Create order
            $pdo->prepare("
                INSERT INTO orders (buyer_id, total_amount, status, payment_method, payment_status, reference, delivery_address)
                VALUES (?, ?, 'confirmed', 'simulated', 'paid', ?, ?)
            ")->execute([userId(), $total, $reference, $address]);

            $orderId = $pdo->lastInsertId();

            // Order item
            $pdo->prepare("
                INSERT INTO order_items (order_id, listing_id, quantity, unit_price)
                VALUES (?, ?, ?, ?)
            ")->execute([$orderId, $listingId, $qty, $listing['price']]);

            // Reduce stock
            $pdo->prepare("
                UPDATE listings SET quantity = quantity - ?
                WHERE id = ? AND quantity >= ?
            ")->execute([$qty, $listingId, $qty]);

            header('Location: ' . SITE_URL . '/order-confirm.php?ref=' . urlencode($reference));
            exit;
        }
    }
}

$pageTitle = 'Checkout — Voetstoots Exchange';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-4" style="max-width:800px;">
    <h2 class="fw-bold mb-4">Checkout</h2>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0 ps-3">
            <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-md-7">
            <form method="POST" novalidate>
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

                <!-- Delivery details -->
                <div class="bg-white rounded-3 shadow-sm p-4 mb-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-truck me-2 text-success"></i>Delivery Details</h5>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Delivery Address</label>
                        <textarea class="form-control" name="delivery_address" rows="3" required
                                  placeholder="Street, suburb, city, postal code..."><?= htmlspecialchars($_POST['delivery_address'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Quantity</label>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm qty-decrease" data-target="qty-input">
                                <i class="bi bi-dash"></i>
                            </button>
                            <input type="number" class="form-control form-control-sm text-center"
                                   id="qty-input" name="quantity" style="width:70px;"
                                   min="1" max="<?= $listing['quantity'] ?>" value="1">
                            <button type="button" class="btn btn-outline-secondary btn-sm qty-increase" data-target="qty-input">
                                <i class="bi bi-plus"></i>
                            </button>
                            <small class="text-muted">(<?= $listing['quantity'] ?> available)</small>
                        </div>
                    </div>
                </div>

                <!-- Payment method -->
                <div class="bg-white rounded-3 shadow-sm p-4 mb-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-credit-card me-2 text-success"></i>Payment Method</h5>
                    <div class="payment-option selected">
                        <input type="radio" name="payment_method" value="simulated" checked class="form-check-input me-2">
                        <strong>Simulated Payment</strong>
                        <small class="d-block text-muted mt-1">
                            This is a demonstration platform. No real payment will be processed.
                            Your order will be confirmed immediately.
                        </small>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold fs-5">
                    <i class="bi bi-bag-check me-2"></i>Confirm Order
                </button>
            </form>
        </div>

        <!-- Order summary -->
        <div class="col-md-5">
            <div class="order-summary">
                <h5 class="fw-bold mb-3">Order Summary</h5>
                <div class="d-flex gap-3 mb-3">
                    <img src="<?= $listing['image']
                        ? SITE_URL . '/uploads/listings/' . htmlspecialchars($listing['image'])
                        : SITE_URL . '/assets/img/placeholder.jpg' ?>"
                         style="width:80px;height:80px;object-fit:cover;border-radius:8px;" alt="">
                    <div>
                        <p class="fw-semibold mb-1 small"><?= htmlspecialchars($listing['title']) ?></p>
                        <small class="text-muted d-block"><?= htmlspecialchars($listing['category_name']) ?></small>
                        <small class="text-muted d-block">Seller: <?= htmlspecialchars($listing['seller_name']) ?></small>
                    </div>
                </div>
                <hr>
                <div class="d-flex justify-content-between small mb-1">
                    <span>Unit Price</span><span>R <?= number_format($listing['price'], 2) ?></span>
                </div>
                <div class="d-flex justify-content-between small mb-1">
                    <span>Delivery</span><span class="text-success">Arranged with seller</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between fw-bold">
                    <span>Total</span>
                    <span class="text-success" id="checkout-total">R <?= number_format($listing['price'], 2) ?></span>
                </div>
                <p class="text-muted small mt-3 mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Delivery is arranged directly between buyer and seller after the order is confirmed.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
// Update total as quantity changes
document.getElementById('qty-input').addEventListener('input', function () {
    const unitPrice = <?= $listing['price'] ?>;
    const qty = parseInt(this.value) || 1;
    document.getElementById('checkout-total').textContent = 'R ' + (unitPrice * qty).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
