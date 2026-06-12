<?php
// ============================================================
// includes/auth.php — Session management and access control
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Returns true if a user is logged in
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

// Returns the current user's role or null
function userRole(): ?string {
    return $_SESSION['user_role'] ?? null;
}

// Returns the current user's ID or null
function userId(): ?int {
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

// Redirect to login if not authenticated
function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

// Redirect if user is not a seller
function requireSeller(): void {
    requireLogin();
    if (userRole() !== 'seller' && userRole() !== 'admin') {
        header('Location: ' . SITE_URL . '/index.php?error=access_denied');
        exit;
    }
}

// Redirect if user is not an admin
function requireAdmin(): void {
    if (!isLoggedIn() || userRole() !== 'admin') {
        header('Location: ' . SITE_URL . '/admin/login.php');
        exit;
    }
}

// Log a user into the session
function loginUser(array $user): void {
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_name']  = $user['full_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role']  = $user['role'];
}

// Destroy the session on logout
function logoutUser(): void {
    session_unset();
    session_destroy();
}

// Generate a simple CSRF token
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Validate a submitted CSRF token
function validateCsrf(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
