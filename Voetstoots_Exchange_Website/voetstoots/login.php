<?php
// ============================================================
// login.php — User Login
// ============================================================
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

$error    = '';
$redirect = $_GET['redirect'] ?? SITE_URL . '/index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid form submission. Please try again.';
    } else {
        $email    = trim($_POST['email']    ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            loginUser($user);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Welcome back, ' . $user['full_name'] . '!'];
            $safeRedirect = filter_var($redirect, FILTER_VALIDATE_URL) ? $redirect : SITE_URL . '/index.php';
            header('Location: ' . $safeRedirect);
            exit;
        } else {
            $error = 'Incorrect email address or password. Please try again.';
        }
    }
}

$pageTitle = 'Login — Voetstoots Exchange';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5" style="max-width:460px;">
    <div class="form-card">
        <div class="text-center mb-4">
            <i class="bi bi-box-arrow-in-right fs-1 text-success"></i>
            <h2 class="fw-bold mt-2">Sign in</h2>
            <p class="text-muted small">
                <span class="zulu-label">Ngena ngemvume</span> — Access your account
            </p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <input type="hidden" name="csrf_token"  value="<?= csrfToken() ?>">
            <input type="hidden" name="redirect"    value="<?= htmlspecialchars($redirect) ?>">

            <div class="mb-3">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" class="form-control" id="email" name="email"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label" for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
            </button>
        </form>

        <p class="text-center mt-3 mb-0 small text-muted">
            Don't have an account? <a href="register.php">Register here</a>
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
