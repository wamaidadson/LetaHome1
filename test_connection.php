<?php
require_once 'php/config.php';

// Test DB connection
echo "<h2>🧪 DB Connection Test</h2>";

if ($conn->ping()) {
    echo "<p style='color: green;'>✅ Connected to DB: " . DB_NAME . "</p>";
    
    // Test users table
    $result = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
    if (mysqli_num_rows($result) > 0) {
        echo "<p style='color: green;'>✅ Users table exists</p>";
        
        // Check admin user
        $admin = mysqli_query($conn, "SELECT username FROM users WHERE username = 'admin'");
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
    $tables = mysqli_query($conn, "SHOW TABLES");
    while ($row = mysqli_fetch_array($tables)) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
    
} else {
    echo "<p style='color: red;'>❌ DB connection failed: " . mysqli_error($conn) . "</p>";
    echo "<p>Check php/config.php DB creds</p>";
}

echo "<hr>";
echo "<p><a href='html/login.html'>Test Login</a> | <a href='add_admin_user.php'>Add Admin</a></p>";
?>

