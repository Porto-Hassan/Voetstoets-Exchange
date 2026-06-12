<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
logoutUser();
header('Location: ' . SITE_URL . '/admin/login.php');
exit;
