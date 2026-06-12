<?php
// ============================================================
// admin/login.php — Admin Login (separate from main site login)
// ============================================================
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (isLoggedIn() && userRole() === 'admin') {
    header('Location: ' . SITE_URL . '/admin/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid form submission.';
    } else {
        $email    = trim($_POST['email']    ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin' AND is_active = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            loginUser($user);
            header('Location: ' . SITE_URL . '/admin/index.php');
            exit;
        } else {
            $error = 'Invalid admin credentials.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — Voetstoots Exchange</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 bg-dark">

<div style="width:100%;max-width:400px;padding:1.5rem;">
    <div class="text-center mb-4">
        <i class="bi bi-shield-lock-fill text-warning" style="font-size:3rem;"></i>
        <h3 class="text-white fw-bold mt-2">Admin Access</h3>
        <p class="text-white-50 small">Voetstoots Exchange Administration</p>
    </div>
    <div class="form-card">
        <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" novalidate>
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <div class="mb-3">
                <label class="form-label fw-semibold">Admin Email</label>
                <input type="email" class="form-control" name="email" required autofocus
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">Password</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            <button type="submit" class="btn btn-warning w-100 py-2 fw-semibold">
                <i class="bi bi-shield-lock me-2"></i>Sign In to Admin
            </button>
        </form>
    </div>
    <p class="text-center mt-3 mb-0">
        <a href="<?= SITE_URL ?>/index.php" class="text-white-50 small">
            <i class="bi bi-arrow-left me-1"></i>Back to main site
        </a>
    </p>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
