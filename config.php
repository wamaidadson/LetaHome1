<?php
// config.php - Database configuration for Leta Homes Agency

// Enable error reporti ng for development (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration - Supports Render/Clever Cloud env vars with local fallback
define('DB_SERVER', $_ENV['MYSQL_ADDON_HOST'] ?? 'localhost');
define('DB_USERNAME', $_ENV['MYSQL_ADDON_USER'] ?? 'root');
define('DB_PASSWORD', $_ENV['MYSQL_ADDON_PASSWORD'] ?? '');
define('DB_NAME', $_ENV['MYSQL_ADDON_DB'] ?? 'leta_homes');
define('DB_PORT', $_ENV['MYSQL_ADDON_PORT'] ?? '3306');

// Set timezone
date_default_timezone_set('Africa/Nairobi');

/**
 * Require login - redirect to login page if not logged in
 */
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Format money with KSh currency
 * @param float $amount Amount to format
 * @return string Formatted currency
 */
function formatMoney($amount) {
    return 'KSh ' . number_format($amount, 2);
}

/**
 * Generate unique tenant ID
 * @param mysqli $conn Database connection
 * @return string Generated tenant ID
 */
function generateTenantID($conn) {
    $year = date('Y');
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM tenants WHERE tenant_id LIKE 'TH-$year%'");
    $row = mysqli_fetch_assoc($result);
    $count = $row['count'] + 1;
    return 'TH-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
}

/**
 * Generate unique receipt number
 * @param mysqli $conn Database connection
 * @return string Generated receipt number
 */
function generateReceiptNumber($conn) {
    $year = date('Y');
    $month = date('m');
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM receipts WHERE receipt_number LIKE 'RCP-$year$month%'");
    $row = mysqli_fetch_assoc($result);
    $count = $row['count'] + 1;
    return 'RCP-' . $year . $month . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
}

/**
 * Create database connection with error handling
 * @return mysqli Returns MySQLi connection object
 * @throws Exception If connection fails
 */
function getDatabaseConnection() {
    try {
        // Create connection with mysqli
        $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);
        
        // Check connection
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        // Set charset to UTF-8
        if (!$conn->set_charset("utf8mb4")) {
            throw new Exception("Error setting charset: " . $conn->error);
        }
        
        return $conn;
        
    } catch (Exception $e) {
        // Log error to file
        error_log("Database Connection Error: " . $e->getMessage());
        
        // Return error for display (remove in production)
        die("
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; background: #fff5f5; border: 2px solid #fc8181; border-radius: 10px;'>
                <h2 style='color: #c53030; margin-top: 0;'>⚠️ Database Connection Error</h2>
                <p style='color: #4a5568; line-height: 1.6;'>Could not connect to the database. Please check:</p>
                <ul style='color: #4a5568; line-height: 1.8;'>
                    <li>✅ XAMPP/WAMP is running (Apache & MySQL)</li>
                    <li>✅ MySQL service is started</li>
                    <li>✅ Database '" . DB_NAME . "' exists</li>
                    <li>✅ Username and password are correct</li>
                </ul>
                <p style='background: #fed7d7; padding: 10px; border-radius: 5px; color: #742a2a;'>
                    <strong>Error details:</strong> " . $e->getMessage() . "
                </p>
                <div style='margin-top: 20px;'>
                    <a href='setup_database.php' style='display: inline-block; background: #4299e1; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Run Database Setup</a>
                    <a href='test_connection.php' style='display: inline-block; background: #48bb78; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Connection</a>
                </div>
            </div>
        ");
    }
}

/**
 * Initialize database connection
 */
$conn = getDatabaseConnection();

/**
 * Sanitize input data to prevent SQL injection
 * @param string $data Input data
 * @return string Sanitized data
 */
function sanitizeInput($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = $conn->real_escape_string($data);
    return $data;
}
?>

