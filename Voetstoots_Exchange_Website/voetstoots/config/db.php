<?php
// ============================================================
// config/db.php — Database connection
// Update DB_HOST, DB_NAME, DB_USER, DB_PASS for InfinityFree
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'voetstoots_db');
define('DB_USER', 'root');           // Replace with your InfinityFree DB username
define('DB_PASS', '');               // Replace with your InfinityFree DB password

define('SITE_NAME', 'Voetstoots Exchange');
define('SITE_URL',  'http://localhost/voetstoots'); // Replace with your live URL

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}
