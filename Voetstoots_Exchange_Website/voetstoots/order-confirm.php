<?php
// ============================================================
// order-confirm.php — Order Confirmation Page
// ============================================================
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$ref = trim($_GET['ref'] ?? '');

$stmt = $pdo->prepare("
    SELECT o.*, oi.quantity, oi.unit_price,
           l.title AS listing_title, l.image AS listing_image,
           u.full_name AS seller_name, u.phone AS seller_phone,
           u.location AS seller_location
    FROM orders o
    JOIN order_items oi ON oi.order_id   = o.id
    JOIN listings l     ON oi.listing_id = l.id
    JOIN users u        ON l.seller_id   = u.id
    WHERE o.reference = ? AND o.buyer_id = ?
");
$stmt->execute([$ref, userId()]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

$pageTitle = 'Order Confirmed — Voetstoots Exchange';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5" style="max-width:640px;">
    <div class="text-center mb-5">
        <div class="text-success mb-3">
            <i class="bi bi-check-circle-fill" style="font-size:5rem;"></i>
        </div>
        <h2 class="fw-bold">Order Confirmed!</h2>
        <p class="text-muted">
            <span class="zulu-label">Ioda lakho lithe amukiwe</span> — Your order has been received.<br>
            Reference number: <strong><?= htmlspecialchars($order['reference']) ?></strong>
        </p>
    </div>

    <div class="bg-white rounded-3 shadow-sm p-4 mb-4">
        <h5 class="fw-bold mb-3">Order Details</h5>
        <div class="d-flex gap-3 mb-3">
            <img src="<?= $order['listing_image']
                ? SITE_URL . '/uploads/listings/' . htmlspecialchars($order['listing_image'])
                : SITE_URL . '/assets/img/placeholder.jpg' ?>"
                 style="width:80px;height:80px;object-fit:cover;border-radius:8px;" alt="">
            <div>
                <p class="fw-semibold mb-1"><?= htmlspecialchars($order['listing_title']) ?></p>
                <small class="text-muted d-block">Qty: <?= $order['quantity'] ?></small>
                <small class="text-muted d-block">
                    Unit price: R <?= number_format($order['unit_price'], 2) ?>
                </small>
            </div>
        </div>
        <hr>
        <div class="row small">
            <div class="col-6"><strong>Order Total</strong></div>
            <div class="col-6 text-end text-success fw-bold">R <?= number_format($order['total_amount'], 2) ?></div>

            <div class="col-6 mt-2"><strong>Payment</strong></div>
            <div class="col-6 mt-2 text-end">
                <span class="badge bg-success">Paid (Simulated)</span>
            </div>

            <div class="col-6 mt-2"><strong>Status</strong></div>
            <div class="col-6 mt-2 text-end">
                <span class="badge bg-primary">Confirmed</span>
            </div>

            <div class="col-6 mt-2"><strong>Date</strong></div>
            <div class="col-6 mt-2 text-end"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></div>
        </div>
    </div>

    <div class="bg-white rounded-3 shadow-sm p-4 mb-4">
        <h5 class="fw-bold mb-3"><i class="bi bi-person-check me-2 text-success"></i>Seller Contact</h5>
        <p class="mb-1"><strong><?= htmlspecialchars($order['seller_name']) ?></strong></p>
        <?php if ($order['seller_phone']): ?>
        <p class="small text-muted mb-1">
            <i class="bi bi-telephone me-1"></i><?= htmlspecialchars($order['seller_phone']) ?>
        </p>
        <?php endif; ?>
        <?php if ($order['seller_location']): ?>
        <p class="small text-muted mb-0">
            <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($order['seller_location']) ?>
        </p>
        <?php endif; ?>
        <p class="small text-muted mt-2 mb-0">
            <i class="bi bi-info-circle me-1"></i>
            Contact the seller directly to arrange delivery or collection.
        </p>
    </div>

    <div class="d-flex gap-3 justify-content-center">
        <a href="my-orders.php" class="btn btn-outline-primary">
            <i class="bi bi-bag me-2"></i>View All Orders
        </a>
        <a href="listings.php" class="btn btn-primary">
            <i class="bi bi-grid me-2"></i>Continue Browsing
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
