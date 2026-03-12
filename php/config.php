<?php
// Database configuration for Leta Homes Agency - PostgreSQL (Supabase)
error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('Africa/Nairobi');

// Database credentials - UPDATE [YOUR-PASSWORD]
define('DB_HOST', $_ENV['DB_HOST'] ?? 'db.bvecgfpvogpmapquoaoc.supabase.co');
define('DB_PORT', $_ENV['DB_PORT'] ?? '5432');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'postgres');
define('DB_USER', $_ENV['DB_USER'] ?? 'postgres');
define('DB_PASSWORD', $_ENV['DB_PASSWORD'] ?? '[YOUR-PASSWORD]');

/**
 * Create database connection (PDO PostgreSQL)
 */
function getDatabaseConnection() {
    $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";user=" . DB_USER . ";password=" . DB_PASSWORD;
    
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        error_log("DB Error: " . $e->getMessage());
        die("Database Connection Failed - check password/host");
    }
}

$pdo = getDatabaseConnection();

/**
 * Check if user is logged in
 */
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../html/login.html');
        exit();
    }
}

/**
 * Format money
 */
function formatMoney($amount) {
    return 'KES ' . number_format($amount, 2);
}
?>

