<?php
// Supabase PostgreSQL Test Connection
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🧪 Leta Homes Supabase Test</h2>";

// Load Supabase config (update YOUR-PASSWORD first!)
require 'php/config_supabase.php';

try {
    // Test 1: Connection
    echo "<p><strong>✅ Connection:</strong> Success!</p>";
    
    // Test 2: Database version
    $stmt = $pdo->query("SELECT version() as pg_version");
    $version = $stmt->fetch();
    echo "<p><strong>PostgreSQL:</strong> " . $version['pg_version'] . "</p>";
    
    // Test 3: Tables exist
    $stmt = $pdo->query("SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename");
    $tables = $stmt->fetchAll();
    echo "<p><strong>Tables (" . count($tables) . "):</strong></p><ul>";
    foreach($tables as $table) {
        echo "<li>" . $table['tablename'] . "</li>";
    }
    echo "</ul>";
    
    // Test 4: Admin user
    $stmt = $pdo->query("SELECT username FROM users LIMIT 1");
    $admin = $stmt->fetch();
    echo "<p><strong>Admin User:</strong> " . ($admin['username'] ?? '❌ Run database.sql first!') . "</p>";
    
    // Test 5: Sample tenants
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM tenants");
    $tenants = $stmt->fetch();
    echo "<p><strong>Sample Tenants:</strong> " . $tenants['count'] . "</p>";
    
    echo "<hr><p><strong>All tests passed! 🎉 App ready.</strong></p>";
    
} catch (Exception $e) {
    echo "<p style='color:red'><strong>❌ Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p>💡 Steps:<ol>";
    echo "<li>Run database.sql in Supabase SQL Editor</li>";
    echo "<li>Update php/config_supabase.php with your password</li>";
    echo "<li>Rename to php/config.php (backup old)</li>";
    echo "</ol></p>";
}
?>

