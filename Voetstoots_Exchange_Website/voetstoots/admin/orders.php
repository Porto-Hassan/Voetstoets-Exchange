<?php
// ============================================================
// admin/orders.php — Manage All Orders
// ============================================================
$pageTitle = 'Manage Orders';
require_once __DIR__ . '/includes/admin-header.php';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCsrf($_POST['csrf_token'] ?? '')) {
    $oid       = (int) ($_POST['order_id'] ?? 0);
    $newStatus = $_POST['new_status'] ?? '';
    $allowed   = ['pending','confirmed','shipped','delivered','cancelled'];
    if ($oid && in_array($newStatus, $allowed)) {
        $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$newStatus, $oid]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Order status updated.'];
    }
    header('Location: ' . SITE_URL . '/admin/orders.php');
    exit;
}

$statusFilter = $_GET['status'] ?? '';
$where  = ['1=1'];
$params = [];
if ($statusFilter && in_array($statusFilter, ['pending','confirmed','shipped','delivered','cancelled'])) {
    $where[]  = "o.status = ?";
    $params[] = $statusFilter;
}

$stmt = $pdo->prepare("
    SELECT o.*, u.full_name AS buyer_name, u.email AS buyer_email,
           l.title AS listing_title, oi.quantity, oi.unit_price
    FROM orders o
    JOIN users u        ON o.buyer_id    = u.id
    JOIN order_items oi ON oi.order_id   = o.id
    JOIN listings l     ON oi.listing_id = l.id
    WHERE " . implode(' AND ', $where) . "
    ORDER BY o.created_at DESC
");
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Manage Orders</h2>
    <span class="text-muted small"><?= count($orders) ?> order(s)</span>
</div>

<!-- Filter tabs -->
<div class="d-flex gap-2 mb-4 flex-wrap">
    <?php foreach (['' => 'All','pending' => 'Pending','confirmed' => 'Confirmed','shipped' => 'Shipped','delivered' => 'Delivered','cancelled' => 'Cancelled'] as $val => $label): ?>
    <a href="orders.php?status=<?= $val ?>"
       class="btn btn-sm <?= $statusFilter === $val ? 'btn-primary' : 'btn-outline-secondary' ?>">
        <?= $label ?>
    </a>
    <?php endforeach; ?>
</div>

<div class="bg-white rounded-3 shadow-sm p-4">
    <?php if (empty($orders)): ?>
    <p class="text-muted text-center py-3">No orders found.</p>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Reference</th>
                    <th>Buyer</th>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Update</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                <tr>
                    <td class="small fw-semibold text-muted"><?= htmlspecialchars($o['reference']) ?></td>
                    <td>
                        <div class="small fw-semibold"><?= htmlspecialchars($o['buyer_name']) ?></div>
                        <div class="small text-muted"><?= htmlspecialchars($o['buyer_email']) ?></div>
                    </td>
                    <td class="small"><?= htmlspecialchars(mb_strimwidth($o['listing_title'], 0, 35, '...')) ?></td>
                    <td class="small"><?= $o['quantity'] ?></td>
                    <td class="small fw-semibold text-success">R <?= number_format($o['total_amount'], 2) ?></td>
                    <td>
                        <span class="badge <?= $o['payment_status'] === 'paid' ? 'bg-success' : 'bg-warning text-dark' ?>">
                            <?= ucfirst($o['payment_status']) ?>
                        </span>
                    </td>
                    <td>
                        <span class="status-badge status-<?= $o['status'] === 'delivered' ? 'active' : ($o['status'] === 'cancelled' ? 'removed' : 'pending') ?>">
                            <?= ucfirst($o['status']) ?>
                        </span>
                    </td>
                    <td class="small text-muted"><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                    <td>
                        <form method="POST" class="d-flex gap-1">
                            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                            <input type="hidden" name="order_id"   value="<?= $o['id'] ?>">
                            <select name="new_status" class="form-select form-select-sm" style="width:130px;">
                                <?php foreach (['pending','confirmed','shipped','delivered','cancelled'] as $s): ?>
                                <option value="<?= $s ?>" <?= $o['status'] === $s ? 'selected' : '' ?>>
                                    <?= ucfirst($s) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn btn-sm btn-primary" title="Update">
                                <i class="bi bi-check-lg"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
