<?php
// Database configuration for Leta Homes Agency
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'leta_homes');
define('DB_PORT', '3306');

date_default_timezone_set('Africa/Nairobi');

/**
 * Create database connection
 */
function getDatabaseConnection() {
    try {
        $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        if (!$conn->set_charset("utf8mb4")) {
            throw new Exception("Error setting charset: " . $conn->error);
        }
        
        return $conn;
        
    } catch (Exception $e) {
        error_log("Database Connection Error: " . $e->getMessage());
        die("Database Connection Error: " . $e->getMessage());
    }
}

$conn = getDatabaseConnection();

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

/**
 * Generate unique tenant ID
 */
function generateTenantID($conn) {
    $year = date('Y');
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM tenants WHERE tenant_id LIKE 'TEN-$year%'");
    $row = mysqli_fetch_assoc($result);
    $num = $row['count'] + 1;
    return 'TEN-' . $year . str_pad($num, 4, '0', STR_PAD_LEFT);
}

/**
 * Generate receipt number
 */
function generateReceiptNumber($conn) {
    $year = date('Y');
    $month = date('m');
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM receipts WHERE receipt_number LIKE 'RCP-$year$month%'");
    $row = mysqli_fetch_assoc($result);
    $num = $row['count'] + 1;
    return 'RCP-' . $year . $month . str_pad($num, 4, '0', STR_PAD_LEFT);
}
?>
