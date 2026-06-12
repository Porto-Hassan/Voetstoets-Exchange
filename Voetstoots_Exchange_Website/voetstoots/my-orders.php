<?php
// ============================================================
// my-orders.php — Buyer's Order History
// ============================================================
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$stmt = $pdo->prepare("
    SELECT o.*, oi.quantity, oi.unit_price,
           l.title AS listing_title, l.image AS listing_image, l.id AS listing_id
    FROM orders o
    JOIN order_items oi ON oi.order_id   = o.id
    JOIN listings l     ON oi.listing_id = l.id
    WHERE o.buyer_id = ?
    ORDER BY o.created_at DESC
");
$stmt->execute([userId()]);
$orders = $stmt->fetchAll();

$pageTitle = 'My Orders — Voetstoots Exchange';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-4">
    <h2 class="fw-bold mb-1">My Orders</h2>
    <p class="text-muted small mb-4">
        <span class="zulu-label">Izinto engazithenga</span> — Your purchase history
    </p>

    <?php if (empty($orders)): ?>
    <div class="text-center py-5 text-muted">
        <i class="bi bi-bag fs-1 d-block mb-3"></i>
        <p>You have not placed any orders yet.</p>
        <a href="listings.php" class="btn btn-primary btn-sm">Start Browsing</a>
    </div>
    <?php else: ?>
    <div class="row g-3">
        <?php foreach ($orders as $o): ?>
        <div class="col-12">
            <div class="bg-white rounded-3 shadow-sm p-4">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                    <div>
                        <small class="text-muted">Reference</small>
                        <div class="fw-semibold"><?= htmlspecialchars($o['reference']) ?></div>
                    </div>
                    <div class="text-end">
                        <span class="status-badge status-<?= $o['status'] ?> me-2">
                            <?= ucfirst($o['status']) ?>
                        </span>
                        <span class="badge bg-<?= $o['payment_status'] === 'paid' ? 'success' : 'warning' ?>">
                            <?= ucfirst($o['payment_status']) ?>
                        </span>
                    </div>
                </div>

                <div class="d-flex gap-3 align-items-center">
                    <img src="<?= $o['listing_image']
                        ? SITE_URL . '/uploads/listings/' . htmlspecialchars($o['listing_image'])
                        : SITE_URL . '/assets/img/placeholder.jpg' ?>"
                         style="width:64px;height:64px;object-fit:cover;border-radius:8px;" alt="">
                    <div class="flex-grow-1">
                        <a href="listing.php?id=<?= $o['listing_id'] ?>" class="fw-semibold text-decoration-none">
                            <?= htmlspecialchars($o['listing_title']) ?>
                        </a>
                        <div class="small text-muted">
                            Qty: <?= $o['quantity'] ?> &bull;
                            Unit: R <?= number_format($o['unit_price'], 2) ?> &bull;
                            <?= date('d M Y', strtotime($o['created_at'])) ?>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="price">R <?= number_format($o['total_amount'], 2) ?></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
