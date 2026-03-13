<?php
// Test DB Connection for Leta Homes Agency
echo "<h2>🧪 Database Connection Test</h2>";

// Test MySQLi ($conn)
if (isset($conn)) {
    echo "<h3>✅ MySQLi Connection: ";
    if ($conn->ping()) {
        echo "ACTIVE</h3>";
        $result = $conn->query("SELECT DATABASE() as db");
        $row = $result->fetch_assoc();
        echo "<p>📂 Current DB: " . htmlspecialchars($row['db']) . "</p>";
        echo "<p>📊 Tables: ";
        $tables = $conn->query("SHOW TABLES");
        if ($tables->num_rows > 0) {
            echo $tables->num_rows . " tables (e.g. plots, users)</p>";
            $result = $conn->query("DESCRIBE plots");
            if ($result && $result->num_rows > 0) {
                echo "<p>✅ plots table exists with " . $result->num_rows . " columns</p>";
            }
        } else {
            echo "0 tables (run setup_database_mysql.php first)</p>";
        }
        echo "<p>👤 Sample users: ";
        $users = $conn->query("SELECT COUNT(*) as count FROM users");
        echo $users->fetch_assoc()['count'] . "</p>";
    } else {
        echo "FAILED</h3><p>❌ Run setup_database_mysql.php or check XAMPP MySQL.</p>";
    }
} else {
    echo "❌ $conn not defined</h3>";
}

// Test add_plot endpoint
echo "<hr><h3>🧪 Test Add Plot (POST)</h3>";
echo "<form method='POST'>
    Plot Name: <input name='plot_name' value='Test Plot 1' required><br>
    Location: <input name='location' value='Test Location'><br>
    <button type='submit'>Test Add Plot → Check phpMyAdmin plots table</button>
</form>";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    echo "<hr><h4>Test Result:</h4><pre>";
    // Simulate add_plot logic
    $plot_name = trim($_POST['plot_name']);
    $stmt = $conn->prepare("INSERT INTO plots (plot_name, location) VALUES (?, ?)");
    $location = trim($_POST['location']);
    $stmt->bind_param("ss", $plot_name, $location);
    if ($stmt->execute()) {
        echo "✅ Plot added! ID: " . $conn->insert_id . "\n";
    } else {
        echo "❌ Error: " . $stmt->error . "\n";
    }
    $stmt->close();
    echo "</pre>";
}

echo "<hr><p><a href='setup_database_mysql.php'>🔄 Run Setup First</a> | <a href='index.php'>🏠 Dashboard</a></p>";
?>

