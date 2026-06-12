<?php
// ============================================================
// admin/users.php — Manage Users (CRUD + Activate/Deactivate)
// ============================================================
$pageTitle = 'Manage Users';
require_once __DIR__ . '/includes/admin-header.php';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCsrf($_POST['csrf_token'] ?? '')) {
    $uid    = (int) ($_POST['user_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    // Prevent admin from modifying their own account via this panel
    if ($uid !== userId()) {
        switch ($action) {
            case 'activate':
                $pdo->prepare("UPDATE users SET is_active = 1 WHERE id = ?")->execute([$uid]);
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'User activated.'];
                break;
            case 'deactivate':
                $pdo->prepare("UPDATE users SET is_active = 0 WHERE id = ?")->execute([$uid]);
                $_SESSION['flash'] = ['type' => 'warning', 'message' => 'User deactivated.'];
                break;
            case 'delete':
                $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'")->execute([$uid]);
                $_SESSION['flash'] = ['type' => 'danger', 'message' => 'User deleted.'];
                break;
            case 'make_seller':
                $pdo->prepare("UPDATE users SET role = 'seller' WHERE id = ?")->execute([$uid]);
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'User upgraded to seller.'];
                break;
        }
    }
    header('Location: ' . SITE_URL . '/admin/users.php');
    exit;
}

// Filters
$roleFilter   = $_GET['role']   ?? '';
$searchFilter = trim($_GET['q'] ?? '');

$where  = ["role != 'admin'"];
$params = [];

if ($roleFilter && in_array($roleFilter, ['buyer', 'seller'])) {
    $where[]  = "role = ?";
    $params[] = $roleFilter;
}
if ($searchFilter) {
    $where[]  = "(full_name LIKE ? OR email LIKE ?)";
    $params[] = "%$searchFilter%";
    $params[] = "%$searchFilter%";
}

$sql   = "SELECT * FROM users WHERE " . implode(' AND ', $where) . " ORDER BY created_at DESC";
$stmt  = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold mb-0">Manage Users</h2>
    <span class="text-muted small"><?= count($users) ?> user(s) found</span>
</div>

<!-- Filter bar -->
<div class="bg-white rounded-3 shadow-sm p-3 mb-4">
    <form method="GET" class="d-flex gap-2 flex-wrap align-items-end">
        <div>
            <label class="form-label small mb-1">Search</label>
            <input type="text" class="form-control form-control-sm" name="q"
                   placeholder="Name or email..." value="<?= htmlspecialchars($searchFilter) ?>">
        </div>
        <div>
            <label class="form-label small mb-1">Role</label>
            <select name="role" class="form-select form-select-sm" style="width:140px;">
                <option value="">All Roles</option>
                <option value="buyer"  <?= $roleFilter === 'buyer'  ? 'selected' : '' ?>>Buyers</option>
                <option value="seller" <?= $roleFilter === 'seller' ? 'selected' : '' ?>>Sellers</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <a href="users.php" class="btn btn-outline-secondary btn-sm">Clear</a>
    </form>
</div>

<div class="bg-white rounded-3 shadow-sm p-4">
    <?php if (empty($users)): ?>
    <p class="text-muted text-center py-3">No users found.</p>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center fw-bold"
                                 style="width:36px;height:36px;font-size:1rem;flex-shrink:0;">
                                <?= strtoupper(substr($u['full_name'], 0, 1)) ?>
                            </div>
                            <span class="fw-semibold small"><?= htmlspecialchars($u['full_name']) ?></span>
                        </div>
                    </td>
                    <td class="small"><?= htmlspecialchars($u['email']) ?></td>
                    <td>
                        <span class="badge <?= $u['role'] === 'seller' ? 'bg-success' : 'bg-secondary' ?>">
                            <?= ucfirst($u['role']) ?>
                        </span>
                    </td>
                    <td class="small text-muted"><?= htmlspecialchars($u['location'] ?? '—') ?></td>
                    <td>
                        <span class="status-badge <?= $u['is_active'] ? 'status-active' : 'status-removed' ?>">
                            <?= $u['is_active'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </td>
                    <td class="small text-muted"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                    <td>
                        <div class="d-flex gap-1 flex-wrap">
                            <?php if ($u['is_active']): ?>
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <input type="hidden" name="action"  value="deactivate">
                                <button class="btn btn-sm btn-outline-warning" title="Deactivate">
                                    <i class="bi bi-person-slash"></i>
                                </button>
                            </form>
                            <?php else: ?>
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <input type="hidden" name="action"  value="activate">
                                <button class="btn btn-sm btn-outline-success" title="Activate">
                                    <i class="bi bi-person-check"></i>
                                </button>
                            </form>
                            <?php endif; ?>

                            <?php if ($u['role'] === 'buyer'): ?>
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <input type="hidden" name="action"  value="make_seller">
                                <button class="btn btn-sm btn-outline-primary" title="Upgrade to Seller">
                                    <i class="bi bi-shop"></i>
                                </button>
                            </form>
                            <?php endif; ?>

                            <form method="POST" class="confirm-delete" data-confirm="Permanently delete this user and all their data?">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <input type="hidden" name="action"  value="delete">
                                <button class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
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
