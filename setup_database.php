<?php
require_once 'php/config.php';

echo "<h2>LETA HOMES Database Setup</h2>";

// Execute the database setup script
$sql = file_get_contents('database.sql');

if ($sql === false) {
    die("Error: Could not read database.sql");
}

if (mysqli_multi_query($conn, $sql)) {
    echo "<p style='color: green;'>✅ Database setup script executed successfully!</p>";
    
    // Show results
    do {
        if ($result = $conn->store_result()) {
            echo "<h3>Result:</h3><pre>";
            while ($row = $result->fetch_assoc()) {
                print_r($row);
            }
            echo "</pre>";
            $result->free();
        }
    } while ($conn->next_result());
} else {
    echo "<p style='color: red;'>❌ Error: " . $conn->error . "</p>";
}

echo "<p><a href='index.php'>→ Go to Dashboard</a></p>";
?>

