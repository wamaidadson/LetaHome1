<?php
require_once 'php/config.php';
$pdo = getDatabaseConnection();

// Test DB connection
echo "<h2>🧪 DB Connection Test</h2>";

if ($pdo) {
    echo "<p style='color: green;'>✅ Connected to DB: " . DB_NAME . "</p>";
    
    // Test users table
$result = $pdo->query("SELECT tablename FROM pg_tables WHERE tablename = 'users'");
    if (mysqli_num_rows($result) > 0) {
        echo "<p style='color: green;'>✅ Users table exists</p>";
        
        // Check admin user
$admin = $pdo->query("SELECT username FROM users WHERE username = 'admin'");
        if (mysqli_num_rows($admin) > 0) {
            echo "<p style='color: green;'>✅ Admin user ready (admin/password)</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ No admin user - run add_admin_user.php</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Users table missing - run setup_database.php</p>";
    }
    
    // List tables
    echo "<h3>Tables:</h3><ul>";
$tables = $pdo->query("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
    while ($row = mysqli_fetch_array($tables)) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
    
} else {
print_r($pdo->errorInfo(), true)
    echo "<p>Check php/config.php DB creds</p>";
}

echo "<hr>";
echo "<p><a href='html/login.html'>Test Login</a> | <a href='add_admin_user.php'>Add Admin</a></p>";
?>

