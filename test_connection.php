<?php
/**
 * Test Database Connection
 * This script tests the database connection and displays status
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>";
echo "<html>";
echo "<head>";
echo "<title>Database Connection Test - Leta Homes Agency</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }";
echo ".success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo ".error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo ".info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }";
echo "h1 { color: #2c3e50; }";
echo "table { border-collapse: collapse; width: 100%; margin-top: 20px; }";
echo "th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }";
echo "th { background-color: #4a5568; color: white; }";
echo "tr:nth-child(even) { background-color: #f2f2f2; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<h1>🗄️ Database Connection Test</h1>";

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "leta_homes";
$port = "3306";

echo "<h2>Configuration:</h2>";
echo "<ul>";
echo "<li><strong>Server:</strong> $servername</li>";
echo "<li><strong>Database:</strong> $dbname</li>";
echo "<li><strong>Username:</strong> $username</li>";
echo "<li><strong>Port:</strong> $port</li>";
echo "</ul>";

// Test connection
echo "<h2>Connection Test:</h2>";
try {
    $conn = new mysqli($servername, $username, $password, $dbname, $port);
    
    if ($conn->connect_error) {
        throw new Exception($conn->connect_error);
    }
    
    echo "<div class='success'>✓ Successfully connected to database '$dbname'</div>";
    
    // Set charset
    if ($conn->set_charset("utf8mb4")) {
        echo "<div class='success'>✓ Character set set to utf8mb4</div>";
    } else {
        echo "<div class='error'>✗ Error setting charset: " . $conn->error . "</div>";
    }
    
    // Check tables
    echo "<h2>Tables Check:</h2>";
    $result = $conn->query("SHOW TABLES");
    if ($result->num_rows > 0) {
        echo "<table>";
        echo "<tr><th>Table Name</th><th>Records</th></tr>";
        while ($row = $result->fetch_array()) {
            $tableName = $row[0];
            $countResult = $conn->query("SELECT COUNT(*) as count FROM `$tableName`");
            $countRow = $countResult->fetch_assoc();
            echo "<tr><td>$tableName</td><td>" . $countRow['count'] . "</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='error'>✗ No tables found in database</div>";
        echo "<p>Please run <a href='setup_database.php'>setup_database.php</a> first!</p>";
    }
    
    // Check users table
    echo "<h2>User Account Check:</h2>";
    $result = $conn->query("SELECT * FROM users LIMIT 1");
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo "<div class='success'>✓ User account found: <strong>" . $user['username'] . "</strong></div>";
    } else {
        echo "<div class='error'>✗ No user accounts found</div>";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<div class='error'>✗ Connection failed: " . $e->getMessage() . "</div>";
    echo "<h3>Troubleshooting:</h3>";
    echo "<ul>";
    echo "<li>✓ Is XAMPP Apache running?</li>";
    echo "<li>✓ Is XAMPP MySQL running?</li>";
    echo "<li>✓ Have you run <a href='setup_database.php'>setup_database.php</a>?</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<p style='text-align:center;'>";
echo "<a href='setup_database.php' style='display:inline-block; background:#4299e1; color:white; padding:10px 20px; text-decoration:none; border-radius:5px; margin-right:10px;'>Run Database Setup</a>";
echo "<a href='index.php' style='display:inline-block; background:#48bb78; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Go to Home</a>";
echo "</p>";

echo "</body>";
echo "</html>";
?>

