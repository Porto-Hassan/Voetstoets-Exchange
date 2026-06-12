<?php
// ============================================================
// register.php — User Registration (Buyer or Seller)
// ============================================================
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

$errors = [];
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!validateCsrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid form submission. Please try again.';
    } else {
        $formData = [
            'full_name' => trim($_POST['full_name'] ?? ''),
            'email'     => trim($_POST['email']     ?? ''),
            'phone'     => trim($_POST['phone']      ?? ''),
            'location'  => trim($_POST['location']   ?? ''),
            'role'      => in_array($_POST['role'] ?? '', ['buyer', 'seller']) ? $_POST['role'] : 'buyer',
            'password'  => $_POST['password']  ?? '',
            'password2' => $_POST['password2'] ?? '',
        ];

        // Validation
        if (empty($formData['full_name']))  $errors[] = 'Full name is required.';
        if (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email address is required.';
        if (strlen($formData['password']) < 8) $errors[] = 'Password must be at least 8 characters.';
        if ($formData['password'] !== $formData['password2']) $errors[] = 'Passwords do not match.';

        // Check email not already registered
        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$formData['email']]);
            if ($stmt->fetch()) {
                $errors[] = 'An account with this email address already exists.';
            }
        }

        // Insert user
        if (empty($errors)) {
            $stmt = $pdo->prepare("
                INSERT INTO users (full_name, email, password_hash, phone, location, role)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $formData['full_name'],
                $formData['email'],
                password_hash($formData['password'], PASSWORD_BCRYPT),
                $formData['phone']    ?: null,
                $formData['location'] ?: null,
                $formData['role'],
            ]);
            $userId = $pdo->lastInsertId();

            // Auto log in
            $user = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $user->execute([$userId]);
            loginUser($user->fetch());

            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Welcome to Voetstoots Exchange! Your account has been created.'];
            header('Location: ' . SITE_URL . '/index.php');
            exit;
        }
    }
}

$pageTitle = 'Register — Voetstoots Exchange';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5" style="max-width:560px;">
    <div class="form-card">
        <div class="text-center mb-4">
            <i class="bi bi-person-plus-fill fs-1 text-success"></i>
            <h2 class="fw-bold mt-2">Create your account</h2>
            <p class="text-muted small">
                <span class="zulu-label">Yenza i-akhawunti</span> — Join the community
            </p>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

            <!-- Account type -->
            <div class="mb-4">
                <label class="form-label fw-semibold">I want to:</label>
                <div class="d-flex gap-3">
                    <div class="form-check flex-fill border rounded p-3">
                        <input class="form-check-input" type="radio" name="role" id="roleBuyer" value="buyer"
                               <?= (($formData['role'] ?? 'buyer') === 'buyer') ? 'checked' : '' ?>>
                        <label class="form-check-label w-100" for="roleBuyer">
                            <i class="bi bi-bag me-1 text-success"></i>
                            <strong>Buy goods</strong><br>
                            <small class="text-muted">Browse and purchase from local sellers</small>
                        </label>
                    </div>
                    <div class="form-check flex-fill border rounded p-3">
                        <input class="form-check-input" type="radio" name="role" id="roleSeller" value="seller"
                               <?= (($formData['role'] ?? '') === 'seller') ? 'checked' : '' ?>>
                        <label class="form-check-label w-100" for="roleSeller">
                            <i class="bi bi-shop me-1 text-success"></i>
                            <strong>Sell goods</strong><br>
                            <small class="text-muted">List your products and reach buyers</small>
                        </label>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label" for="full_name">Full Name</label>
                <input type="text" class="form-control" id="full_name" name="full_name"
                       value="<?= htmlspecialchars($formData['full_name'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" class="form-control" id="email" name="email"
                       value="<?= htmlspecialchars($formData['email'] ?? '') ?>" required>
            </div>
            <div class="row g-3 mb-3">
                <div class="col">
                    <label class="form-label" for="phone">Phone Number <small class="text-muted">(optional)</small></label>
                    <input type="tel" class="form-control" id="phone" name="phone"
                           value="<?= htmlspecialchars($formData['phone'] ?? '') ?>">
                </div>
                <div class="col">
                    <label class="form-label" for="location">Location <small class="text-muted">(optional)</small></label>
                    <input type="text" class="form-control" id="location" name="location"
                           placeholder="e.g. Vereeniging, Gauteng"
                           value="<?= htmlspecialchars($formData['location'] ?? '') ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required minlength="8">
                <div class="form-text">Minimum 8 characters.</div>
            </div>
            <div class="mb-4">
                <label class="form-label" for="password2">Confirm Password</label>
                <input type="password" class="form-control" id="password2" name="password2" required>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                <i class="bi bi-person-check me-2"></i>Create Account
            </button>
        </form>

        <p class="text-center mt-3 mb-0 small text-muted">
            Already have an account? <a href="login.php">Sign in</a>
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
