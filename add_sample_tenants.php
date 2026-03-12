<?php
/**
 * Add Sample Tenants
 * This script adds sample tenants for testing
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "leta_homes";
$port = "3306";

try {
    $conn = new mysqli($servername, $username, $password, $dbname, $port);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    echo "<h1>Adding Sample Tenants</h1>";
    
    // Get plot IDs
    $plots = [];
    $result = $conn->query("SELECT id, plot_name FROM plots");
    while ($row = $result->fetch_assoc()) {
        $plots[$row['plot_name']] = $row['id'];
    }
    
    // Sample tenants data
    $tenants = [
        ['TH-2026-0001', $plots['Sunrise Apartments'], 'John Doe', '0722123456', '101', '1 Bedroom', 15000.00, '2026-01-01'],
        ['TH-2026-0002', $plots['Green Valley Heights'], 'Jane Smith', '0722987654', '202', '2 Bedroom', 25000.00, '2026-01-15'],
        ['TH-2026-0003', $plots['Riverside Gardens'], 'Michael Johnson', '0711234567', 'A1', 'Studio', 10000.00, '2026-02-01'],
        ['TH-2026-0004', $plots['Sunrise Apartments'], 'Sarah Williams', '0734567890', '102', '1 Bedroom', 15000.00, '2026-02-15'],
        ['TH-2026-0005', $plots['Metro Plaza'], 'David Brown', '0745678901', '301', '3 Bedroom', 35000.00, '2026-03-01'],
    ];
    
    foreach ($tenants as $tenant) {
        list($tenant_id, $plot_id, $name, $phone, $house, $type, $rent, $move_in) = $tenant;
        
        $sql = "INSERT INTO tenants (tenant_id, plot_id, tenant_name, phone_number, house_number, house_type, rent_amount, commission_percentage, move_in_date, status)
                VALUES ('$tenant_id', $plot_id, '$name', '$phone', '$house', '$type', $rent, 10.00, '$move_in', 'Active')";
        
        if ($conn->query($sql) === TRUE) {
            echo "<p style='color:green;'>✓ Added tenant: $name</p>";
        } else {
            // Check if already exists
            if ($conn->errno == 1062) {
                echo "<p style='color:orange;'>○ Tenant already exists: $tenant_id</p>";
            } else {
                echo "<p style='color:red;'>✗ Error: " . $conn->error . "</p>";
            }
        }
    }
    
    // Show all tenants
    echo "<h2>All Tenants:</h2>";
    $result = $conn->query("SELECT t.*, p.plot_name FROM tenants t JOIN plots p ON t.plot_id = p.id ORDER BY t.id");
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Tenant ID</th><th>Name</th><th>Plot</th><th>House</th><th>Rent</th><th>Status</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['tenant_id'] . "</td>";
        echo "<td>" . $row['tenant_name'] . "</td>";
        echo "<td>" . $row['plot_name'] . "</td>";
        echo "<td>" . $row['house_number'] . "</td>";
        echo "<td>KES " . number_format($row['rent_amount'], 2) . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2 style='color:green;'>✓ Sample tenants added successfully!</h2>";
    echo "<p><a href='test_connection.php'>Back to Test Connection</a></p>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
}
?>

