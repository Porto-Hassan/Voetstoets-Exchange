<?php
// ============================================================
// edit-listing.php — Edit an Existing Listing
// ============================================================
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
requireSeller();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM listings WHERE id = ? AND seller_id = ?");
$stmt->execute([$id, userId()]);
$listing = $stmt->fetch();

if (!$listing) {
    $_SESSION['flash'] = ['type' => 'warning', 'message' => 'Listing not found or you do not have permission to edit it.'];
    header('Location: ' . SITE_URL . '/dashboard.php');
    exit;
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid form submission.';
    } else {
        $title       = trim($_POST['title']       ?? '');
        $description = trim($_POST['description'] ?? '');
        $price       = $_POST['price']       ?? '';
        $quantity    = (int) ($_POST['quantity']  ?? 1);
        $categoryId  = (int) ($_POST['category_id'] ?? 0);
        $location    = trim($_POST['location'] ?? '');

        if (empty($title))       $errors[] = 'Title is required.';
        if (empty($description)) $errors[] = 'Description is required.';
        if (!is_numeric($price) || $price <= 0) $errors[] = 'A valid price is required.';
        if (!$categoryId)        $errors[] = 'Please select a category.';

        $imageName = $listing['image'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'image/webp'];
            $finfo   = finfo_open(FILEINFO_MIME_TYPE);
            $mime    = finfo_file($finfo, $_FILES['image']['tmp_name']);
            finfo_close($finfo);
            if (!in_array($mime, $allowed)) {
                $errors[] = 'Image must be JPEG, PNG or WebP.';
            } elseif ($_FILES['image']['size'] > 3 * 1024 * 1024) {
                $errors[] = 'Image must be smaller than 3MB.';
            } else {
                $ext       = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $imageName = uniqid('listing_', true) . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/uploads/listings/' . $imageName);
            }
        }

        if (empty($errors)) {
            $pdo->prepare("
                UPDATE listings
                SET title = ?, description = ?, price = ?, quantity = ?,
                    category_id = ?, location = ?, image = ?, status = 'pending'
                WHERE id = ? AND seller_id = ?
            ")->execute([$title, $description, (float)$price, $quantity, $categoryId, $location ?: null, $imageName, $id, userId()]);

            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Listing updated and resubmitted for approval.'];
            header('Location: ' . SITE_URL . '/dashboard.php');
            exit;
        }
    }
}

$pageTitle = 'Edit Listing — Voetstoots Exchange';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-4" style="max-width:700px;">
    <div class="form-card">
        <h2 class="fw-bold mb-1">Edit Listing</h2>
        <p class="text-muted small mb-4">Updating this listing will resubmit it for admin approval.</p>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

            <div class="mb-3">
                <label class="form-label fw-semibold">Listing Title</label>
                <input type="text" class="form-control" name="title" maxlength="200"
                       value="<?= htmlspecialchars($listing['title']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Category</label>
                <select class="form-select" name="category_id" required>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"
                        <?= $listing['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Description</label>
                <textarea class="form-control" name="description" rows="4" maxlength="2000" required><?= htmlspecialchars($listing['description']) ?></textarea>
            </div>
            <div class="row g-3 mb-3">
                <div class="col">
                    <label class="form-label fw-semibold">Price (R)</label>
                    <div class="input-group">
                        <span class="input-group-text">R</span>
                        <input type="number" class="form-control" name="price"
                               min="0.01" step="0.01" value="<?= $listing['price'] ?>" required>
                    </div>
                </div>
                <div class="col">
                    <label class="form-label fw-semibold">Quantity</label>
                    <input type="number" class="form-control" name="quantity"
                           min="1" value="<?= $listing['quantity'] ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Location</label>
                <input type="text" class="form-control" name="location"
                       value="<?= htmlspecialchars($listing['location'] ?? '') ?>">
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">Product Image</label>
                <?php if ($listing['image']): ?>
                <div class="mb-2">
                    <img src="<?= SITE_URL . '/uploads/listings/' . htmlspecialchars($listing['image']) ?>"
                         style="max-height:140px;border-radius:8px;" alt="Current image">
                    <small class="d-block text-muted mt-1">Current image. Upload a new one to replace it.</small>
                </div>
                <?php endif; ?>
                <input type="file" class="form-control" id="listing-image" name="image"
                       accept="image/jpeg,image/png,image/webp">
                <img id="image-preview" src="" alt="" class="mt-2 rounded d-none" style="max-height:180px;">
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                <i class="bi bi-check-circle me-2"></i>Save Changes
            </button>
            <a href="dashboard.php" class="btn btn-outline-secondary w-100 mt-2">Cancel</a>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
