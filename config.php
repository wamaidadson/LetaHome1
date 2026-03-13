<?php
/**
 * LETA HOMES AGENCY - WORKING CONFIGURATION
 * Render + Supabase (Project: bvecgfpvogpmapquoaoc)
 * Password contains special characters - using individual env vars
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// ============================================================================
// DATABASE CONNECTION - INDIVIDUAL VARIABLES (Best for special characters)
// ============================================================================

// Get from environment variables
$db_host = $_ENV['SUPABASE_HOST'] ?? 'aws-1-eu-west-1.pooler.supabase.com';
$db_port = $_ENV['SUPABASE_PORT'] ?? '5432';
$db_name = $_ENV['SUPABASE_DB'] ?? 'postgres';
$db_user = $_ENV['SUPABASE_USER'] ?? 'postgres.bvecgfpvogpmapquoaoc';
$db_pass = $_ENV['SUPABASE_PASSWORD'] ?? '';

// If using connection string instead
if (empty($db_pass) && isset($_ENV['SUPABASE_DATABASE_URL'])) {
    $database_url = $_ENV['SUPABASE_DATABASE_URL'];
    $parsed = parse_url($database_url);
    if ($parsed) {
        $db_host = $parsed['host'] ?? $db_host;
        $db_port = $parsed['port'] ?? $db_port;
        $db_name = ltrim($parsed['path'] ?? '/postgres', '/');
        $db_user = $parsed['user'] ?? $db_user;
        $db_pass = $parsed['pass'] ?? '';
    }
}

// Validate
if (empty($db_pass)) {
    die('
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; background: #fff5f5; border: 2px solid #fc8181; border-radius: 10px;">
        <h2 style="color: #c53030;">⚠️ Database Password Not Set</h2>
        <p>Please set the environment variables in Render Dashboard:</p>
        <table style="width:100%; border-collapse: collapse; margin: 10px 0;">
            <tr style="background:#f5f5f5;">
                <td style="padding:8px; border:1px solid #ddd;"><strong>SUPABASE_HOST</strong></td>
                <td style="padding:8px; border:1px solid #ddd;">aws-1-eu-west-1.pooler.supabase.com</td>
            </tr>
            <tr>
                <td style="padding:8px; border:1px solid #ddd;"><strong>SUPABASE_PORT</strong></td>
                <td style="padding:8px; border:1px solid #ddd;">5432</td>
            </tr>
            <tr style="background:#f5f5f5;">
                <td style="padding:8px; border:1px solid #ddd;"><strong>SUPABASE_DB</strong></td>
                <td style="padding:8px; border:1px solid #ddd;">postgres</td>
            </tr>
            <tr>
                <td style="padding:8px; border:1px solid #ddd;"><strong>SUPABASE_USER</strong></td>
                <td style="padding:8px; border:1px solid #ddd;">postgres.bvecgfpvogpmapquoaoc</td>
            </tr>
            <tr style="background:#f5f5f5;">
                <td style="padding:8px; border:1px solid #ddd;"><strong>SUPABASE_PASSWORD</strong></td>
                <td style="padding:8px; border:1px solid #ddd;">@08447619Dw</td>
            </tr>
        </table>
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

    die('
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; background: #fff5f5; border: 2px solid #fc8181; border-radius: 10px;">
        <h2 style="color: #c53030;">❌ Database Connection Failed</h2>
        <p><strong>Error:</strong> ' . htmlspecialchars($error_msg) . '</p>
        <hr>
        <h3>Check these values in Render Dashboard:</h3>
        <table style="width:100%; border-collapse: collapse;">
            <tr><td style="padding:5px; border:1px solid #ddd;">Host</td><td style="padding:5px; border:1px solid #ddd;">' . htmlspecialchars($db_host) . '</td></tr>
            <tr><td style="padding:5px; border:1px solid #ddd;">Port</td><td style="padding:5px; border:1px solid #ddd;">' . htmlspecialchars($db_port) . '</td></tr>
            <tr><td style="padding:5px; border:1px solid #ddd;">Database</td><td style="padding:5px; border:1px solid #ddd;">' . htmlspecialchars($db_name) . '</td></tr>
            <tr><td style="padding:5px; border:1px solid #ddd;">User</td><td style="padding:5px; border:1px solid #ddd;">' . htmlspecialchars($db_user) . '</td></tr>
            <tr><td style="padding:5px; border:1px solid #ddd;">Password</td><td style="padding:5px; border:1px solid #ddd;">' . (empty($db_pass) ? 'NOT SET' : '***') . '</td></tr>
        </table>
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

date_default_timezone_set('Africa/Nairobi');
?>