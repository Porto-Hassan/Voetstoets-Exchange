<?php
// ============================================================
// add-listing.php — Create a New Listing
// ============================================================
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
requireSeller();

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$errors   = [];
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid form submission. Please try again.';
    } else {
        $formData = [
            'title'       => trim($_POST['title']       ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'price'       => $_POST['price']     ?? '',
            'quantity'    => $_POST['quantity']  ?? 1,
            'category_id' => (int) ($_POST['category_id'] ?? 0),
            'location'    => trim($_POST['location'] ?? ''),
        ];

        if (empty($formData['title']))       $errors[] = 'Title is required.';
        if (empty($formData['description'])) $errors[] = 'Description is required.';
        if (!is_numeric($formData['price']) || $formData['price'] <= 0) $errors[] = 'A valid price is required.';
        if (!$formData['category_id'])       $errors[] = 'Please select a category.';

        // Image upload
        $imageName = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'image/webp'];
            $finfo   = finfo_open(FILEINFO_MIME_TYPE);
            $mime    = finfo_file($finfo, $_FILES['image']['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mime, $allowed)) {
                $errors[] = 'Image must be a JPEG, PNG or WebP file.';
            } elseif ($_FILES['image']['size'] > 3 * 1024 * 1024) {
                $errors[] = 'Image must be smaller than 3MB.';
            } else {
                $ext       = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $imageName = uniqid('listing_', true) . '.' . $ext;
                $dest      = __DIR__ . '/uploads/listings/' . $imageName;
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                    $errors[] = 'Failed to upload image. Please try again.';
                    $imageName = null;
                }
            }
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("
                INSERT INTO listings (seller_id, category_id, title, description, price, quantity, location, image, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([
                userId(),
                $formData['category_id'],
                $formData['title'],
                $formData['description'],
                (float) $formData['price'],
                (int)   $formData['quantity'],
                $formData['location'] ?: null,
                $imageName,
            ]);

            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Listing submitted! It will appear once approved by an admin.'];
            header('Location: ' . SITE_URL . '/dashboard.php');
            exit;
        }
    }
}

$pageTitle = 'Add New Listing — Voetstoots Exchange';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-4" style="max-width:700px;">
    <div class="form-card">
        <h2 class="fw-bold mb-1">Post a New Listing</h2>
        <p class="text-muted small mb-4">
            <span class="zulu-label">Thengisa impahla yakho</span> — Sell your goods
        </p>

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
                <label class="form-label fw-semibold" for="title">Listing Title</label>
                <input type="text" class="form-control" id="title" name="title" maxlength="200"
                       placeholder="e.g. Fresh spinach — Vereeniging grown"
                       value="<?= htmlspecialchars($formData['title'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold" for="category_id">Category</label>
                <select class="form-select" id="category_id" name="category_id" required>
                    <option value="">Select a category...</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"
                        <?= ($formData['category_id'] ?? 0) == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold" for="description">Description</label>
                <textarea class="form-control" id="description" name="description"
                          rows="4" maxlength="2000" required
                          placeholder="Describe what you are selling, how it was made, freshness, size, weight, etc."><?= htmlspecialchars($formData['description'] ?? '') ?></textarea>
            </div>

            <div class="row g-3 mb-3">
                <div class="col">
                    <label class="form-label fw-semibold" for="price">Price (R)</label>
                    <div class="input-group">
                        <span class="input-group-text">R</span>
                        <input type="number" class="form-control" id="price" name="price"
                               min="0.01" step="0.01" placeholder="0.00"
                               value="<?= htmlspecialchars($formData['price'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="col">
                    <label class="form-label fw-semibold" for="quantity">Quantity Available</label>
                    <input type="number" class="form-control" id="quantity" name="quantity"
                           min="1" value="<?= (int) ($formData['quantity'] ?? 1) ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold" for="location">Location</label>
                <input type="text" class="form-control" id="location" name="location"
                       placeholder="e.g. Vereeniging, Gauteng"
                       value="<?= htmlspecialchars($formData['location'] ?? '') ?>">
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold" for="listing-image">Product Image</label>
                <input type="file" class="form-control" id="listing-image" name="image"
                       accept="image/jpeg,image/png,image/webp">
                <div class="form-text">JPEG, PNG or WebP. Maximum 3MB.</div>
                <img id="image-preview" src="" alt="" class="mt-2 rounded d-none" style="max-height:180px;">
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                <i class="bi bi-plus-circle me-2"></i>Submit Listing
            </button>
            <a href="dashboard.php" class="btn btn-outline-secondary w-100 mt-2">Cancel</a>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
