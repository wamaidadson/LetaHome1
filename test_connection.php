<?php
/**
 * Test Supabase Connection
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🧪 Leta Homes - Connection Test</h1>";

// Check environment
$host = $_ENV['SUPABASE_HOST'] ?? 'NOT SET';
$port = $_ENV['SUPABASE_PORT'] ?? 'NOT SET';
$db = $_ENV['SUPABASE_DB'] ?? 'NOT SET';
$user = $_ENV['SUPABASE_USER'] ?? 'NOT SET';
$pass = $_ENV['SUPABASE_PASSWORD'] ?? 'NOT SET';

echo "<h3>Environment Variables:</h3>";
echo "<table style='border-collapse:collapse; width:100%; max-width:600px;'>";
echo "<tr style='background:#f5f5f5;'><td style='padding:8px; border:1px solid #ddd;'>SUPABASE_HOST</td><td style='padding:8px; border:1px solid #ddd;'>" . htmlspecialchars($host) . "</td></tr>";
echo "<tr><td style='padding:8px; border:1px solid #ddd;'>SUPABASE_PORT</td><td style='padding:8px; border:1px solid #ddd;'>" . htmlspecialchars($port) . "</td></tr>";
echo "<tr style='background:#f5f5f5;'><td style='padding:8px; border:1px solid #ddd;'>SUPABASE_DB</td><td style='padding:8px; border:1px solid #ddd;'>" . htmlspecialchars($db) . "</td></tr>";
echo "<tr><td style='padding:8px; border:1px solid #ddd;'>SUPABASE_USER</td><td style='padding:8px; border:1px solid #ddd;'>" . htmlspecialchars($user) . "</td></tr>";
echo "<tr style='background:#f5f5f5;'><td style='padding:8px; border:1px solid #ddd;'>SUPABASE_PASSWORD</td><td style='padding:8px; border:1px solid #ddd;'>" . ($pass !== 'NOT SET' ? '***' : 'NOT SET') . "</td></tr>";
echo "</table>";

if ($pass === 'NOT SET') {
    echo "<p style='color:red; margin-top:20px;'>❌ SUPABASE_PASSWORD not set!</p>";
    echo "<p>Set it to: <code>@08447619Dw</code></p>";
    exit;
}

// Try connection
try {
    require_once 'config.php';

    echo "<h3 style='color:green; margin-top:20px;'>✅ SUCCESS! Connected to Supabase</h3>";

    $stmt = $conn->query("SELECT version() as v");
    $version = $stmt->fetch()['v'];
    echo "<p><strong>PostgreSQL:</strong> " . $version . "</p>";

    $stmt = $conn->query("SELECT tablename FROM pg_tables WHERE schemaname='public' ORDER BY tablename");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p><strong>Tables:</strong> " . implode(', ', $tables) . "</p>";

    $stmt = $conn->query("SELECT username FROM users LIMIT 1");
    $admin = $stmt->fetch();
    echo "<p><strong>Admin User:</strong> " . ($admin['username'] ?? 'Not found') . "</p>";

    echo "<hr><a href='login.php' style='background:#667eea; color:white; padding:10px 20px; text-decoration:none; border-radius:5px; display:inline-block;'>Go to Login</a>";

} catch (Exception $e) {
    echo "<h3 style='color:red; margin-top:20px;'>❌ Connection Failed</h3>";
    echo "<p style='color:red;'>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>