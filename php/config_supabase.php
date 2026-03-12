<?php
// Leta Homes Agency - Supabase PostgreSQL Config (PDO)
error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('Africa/Nairobi');

// Supabase PostgreSQL Connection
define('DB_DSN', 'postgresql://postgres.' . ($_ENV['SUPABASE_PASSWORD'] ?? '[YOUR-PASSWORD]') . '@db.bvecgfpvogpmapquoaoc.supabase.co:5432/postgres');
define('DB_PASSWORD', '');  // Replace [YOUR-PASSWORD] above or set env

/**
 * Create PDO PostgreSQL connection
 */
function getDatabaseConnection() {
    try {
        $pdo = new PDO(DB_DSN, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        
        // Set client encoding
        $pdo->exec("SET NAMES 'UTF8'");
        
        return $pdo;
        
    } catch (PDOException $e) {
        error_log("Database Connection Error: " . $e->getMessage());
        die("Database Connection Error: " . $e->getMessage());
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

/**
 * Generate unique tenant ID
 */
function generateTenantID($pdo) {
    $year = date('Y');
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM tenants WHERE tenant_id LIKE 'TEN-$year%'");
    $row = $stmt->fetch();
    $num = $row['count'] + 1;
    return 'TEN-' . $year . str_pad($num, 4, '0', STR_PAD_LEFT);
}

/**
 * Generate receipt number
 */
function generateReceiptNumber($pdo) {
    $year = date('Y');
    $month = date('m');
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM receipts WHERE receipt_number LIKE 'RCP-$year$month%'");
    $row = $stmt->fetch();
    $num = $row['count'] + 1;
    return 'RCP-' . $year . $month . str_pad($num, 4, '0', STR_PAD_LEFT);
}

// Legacy MySQLi compat (replace $conn with $pdo in files)
function mysqli_query($pdo, $sql) { return $pdo->query($sql); }
function mysqli_fetch_assoc($result) { return $result->fetch(); }
?>

