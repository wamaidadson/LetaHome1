<?php
/**
 * LETA HOMES AGENCY - CONFIGURATION
 * Supabase PostgreSQL + Render Deployment
 * 
 * Connection Details:
 * - Host: aws-1-eu-west-1.pooler.supabase.com
 * - Port: 5432
 * - Database: postgres
 * - User: postgres.bvecgfpvogpmapquoaoc
 * - Password: (set in environment variable)
 */

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// ============================================================================
// DATABASE CONNECTION
// ============================================================================

// Get credentials from environment variables (set in Render Dashboard)
$db_host = $_ENV['SUPABASE_HOST'] ?? 'aws-1-eu-west-1.pooler.supabase.com';
$db_port = $_ENV['SUPABASE_PORT'] ?? '5432';
$db_name = $_ENV['SUPABASE_DB'] ?? 'postgres';
$db_user = $_ENV['SUPABASE_USER'] ?? 'postgres.bvecgfpvogpmapquoaoc';
$db_pass = $_ENV['SUPABASE_PASSWORD'] ?? '';

// Alternative: Full connection string
$database_url = $_ENV['SUPABASE_DATABASE_URL'] ?? $_ENV['DATABASE_URL'] ?? null;

if ($database_url) {
    $parsed = parse_url($database_url);
    if ($parsed) {
        $db_host = $parsed['host'] ?? $db_host;
        $db_port = $parsed['port'] ?? $db_port;
        $db_name = ltrim($parsed['path'] ?? '/postgres', '/');
        $db_user = $parsed['user'] ?? $db_user;
        $db_pass = $parsed['pass'] ?? $db_pass;
    }
}

// Validate password is set
if (empty($db_pass)) {
    die('
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; background: #fff5f5; border: 2px solid #fc8181; border-radius: 10px;">
        <h2 style="color: #c53030; margin-top: 0;">⚠️ Database Password Not Set</h2>
        <p>Please set the <strong>SUPABASE_PASSWORD</strong> environment variable in Render Dashboard.</p>
        <p>Go to: Render Dashboard → Your Service → Environment → Add Environment Variable</p>
        <ul>
            <li>Key: <code>SUPABASE_PASSWORD</code></li>
            <li>Value: <code>Admin123</code></li>
        </ul>
    </div>
    ');
}

// Create PDO connection
try {
    $dsn = "pgsql:host={$db_host};port={$db_port};dbname={$db_name};sslmode=require";

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $conn = new PDO($dsn, $db_user, $db_pass, $options);

} catch (PDOException $e) {
    $error_msg = $e->getMessage();
    $help = '';

    if (strpos($error_msg, 'password authentication failed') !== false) {
        $help = '<p style="color: #c53030;"><strong>Password Error:</strong> The password is incorrect.</p>
        <p>Make sure SUPABASE_PASSWORD is set to: <code>Admin123</code></p>';
    } elseif (strpos($error_msg, 'role') !== false && strpos($error_msg, 'does not exist') !== false) {
        $help = '<p style="color: #c53030;"><strong>Username Error:</strong> The username format is wrong.</p>
        <p>Make sure SUPABASE_USER is: <code>postgres.bvecgfpvogpmapquoaoc</code></p>';
    } elseif (strpos($error_msg, 'getaddrinfo') !== false) {
        $help = '<p style="color: #c53030;"><strong>Host Error:</strong> Cannot resolve hostname.</p>
        <p>Make sure SUPABASE_HOST is: <code>aws-1-eu-west-1.pooler.supabase.com</code></p>';
    }

    die('
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; background: #fff5f5; border: 2px solid #fc8181; border-radius: 10px;">
        <h2 style="color: #c53030; margin-top: 0;">⚠️ Database Connection Error</h2>
        <p><strong>Error:</strong> ' . htmlspecialchars($error_msg) . '</p>
        ' . $help . '
        <hr>
        <p style="font-size: 12px; color: #666;">
            Host: ' . htmlspecialchars($db_host) . '<br>
            Port: ' . htmlspecialchars($db_port) . '<br>
            User: ' . htmlspecialchars($db_user) . '<br>
            Database: ' . htmlspecialchars($db_name) . '
        </p>
    </div>
    ');
}

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

function formatMoney($amount) {
    return 'KSh ' . number_format($amount, 2);
}

function generateTenantID($conn) {
    $year = date('Y');
    $stmt = $conn->query("SELECT COUNT(*) as count FROM tenants WHERE tenant_id LIKE 'TH-{$year}%'");
    $row = $stmt->fetch();
    $count = $row['count'] + 1;
    return 'TH-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
}

function generateReceiptNumber($conn) {
    $year = date('Y');
    $month = date('m');
    $stmt = $conn->query("SELECT COUNT(*) as count FROM receipts WHERE receipt_number LIKE 'RCP-{$year}{$month}%'");
    $row = $stmt->fetch();
    $count = $row['count'] + 1;
    return 'RCP-' . $year . $month . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Set timezone
date_default_timezone_set('Africa/Nairobi');
?>