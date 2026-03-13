<?php
require_once 'php/config.php';

echo "<h2>LETA HOMES MySQL Database Setup (XAMPP)</h2>";
echo "<p>Using MySQLi connection from config.php</p>";

// Read MySQL schema
$sql = file_get_contents('database_mysql.sql');

if ($sql === false) {
    die("❌ Error: Could not read database_mysql.sql");
}

// Execute schema
if (mysqli_multi_query($conn, $sql)) {
    echo "<p style='color: green;'>✅ Database tables created successfully!</p>";
    
    // Process all results
    do {
        if ($result = $conn->store_result()) {
            echo "<h4>Setup Result:</h4><pre>";
            while ($row = $result->fetch_assoc()) {
                print_r($row);
            }
            echo "</pre>";
            $result->free();
        }
    } while ($conn->next_result());
    
    echo "<p><strong>✅ Admin login: admin / admin</strong></p>";
    echo "<p><a href='index.php' class='btn btn-success'>→ Go to Dashboard</a></p>";
} else {
    echo "<p style='color: red;'>❌ Setup Error: " . $conn->error . "</p>";
}

$conn->close();
?>

