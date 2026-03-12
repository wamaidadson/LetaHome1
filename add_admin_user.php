<?php
require_once 'php/config.php';

$username = 'admin';
$password = password_hash('password', PASSWORD_DEFAULT);

// Check if user exists
$query = "SELECT id FROM users WHERE username = '$username'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    $user_id = 'USR-2026-0001';
    $full_name = 'Administrator';
    $email = 'admin@leta.com';
    
    $insert = "INSERT INTO users (user_id, full_name, username, email, password) VALUES ('$user_id', '$full_name', '$username', '$email', '$password')";
    
    if (mysqli_query($conn, $insert)) {
        echo "<h2>✅ Admin user created!</h2>";
        echo "<p>Username: <strong>admin</strong></p>";
        echo "<p>Password: <strong>password</strong></p>";
        echo "<p><a href='login.php'>Login Now</a> | <a href='html/login.html'>HTML Login</a></p>";
        
        // Create tables if not exist
        $tables = ['plots', 'tenants', 'rent_payments', 'receipts'];
        foreach ($tables as $table) {
            $check = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
            if (mysqli_num_rows($check) == 0) {
                echo "<p>⚠️ $table table missing - run setup_database.php</p>";
            }
        }
    } else {
        echo "<p>Error: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<h2>Admin user already exists!</h2>";
    echo "<p>Username: admin, Password: password</p>";
    echo "<p><a href='login.php'>Login</a></p>";
}

echo "<p><a href='index.php'>Home</a></p>";
?>
