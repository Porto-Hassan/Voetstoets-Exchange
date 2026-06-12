<?php
// ============================================================
// logout.php — Destroys session and redirects to homepage
// ============================================================
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

logoutUser();
$_SESSION['flash'] = ['type' => 'info', 'message' => 'You have been signed out successfully.'];
header('Location: ' . SITE_URL . '/index.php');
exit;
