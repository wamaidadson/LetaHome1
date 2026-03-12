<?php
/**
 * Add Sample Payments
 * This script adds sample payments for testing
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
    
    echo "<h1>Adding Sample Payments</h1>";
    
    // Sample payments data (tenant_id, payment_date, amount, status, payment_month)
    $payments = [
        [1, '2026-01-05', 15000.00, 'Paid', '2026-01-01'],
        [2, '2026-01-10', 25000.00, 'Paid', '2026-01-01'],
        [3, '2026-01-15', 10000.00, 'Paid', '2026-01-01'],
        [1, '2026-02-05', 15000.00, 'Paid', '2026-02-01'],
        [2, '2026-02-08', 25000.00, 'Paid', '2026-02-01'],
        [4, '2026-02-20', 15000.00, 'Paid', '2026-02-01'],
        [1, '2026-03-03', 15000.00, 'Paid', '2026-03-01'],
        [5, '2026-03-05', 35000.00, 'Paid', '2026-03-01'],
    ];
    
    foreach ($payments as $payment) {
        list($tenant_id, $payment_date, $amount, $status, $payment_month) = $payment;
        
        // Get commission percentage
        $result = $conn->query("SELECT commission_percentage FROM tenants WHERE id = $tenant_id");
        $tenant = $result->fetch_assoc();
        $commission = $tenant['commission_percentage'];
        $commission_amount = ($amount * $commission) / 100;
        
        $sql = "INSERT INTO rent_payments (tenant_id, payment_date, amount_paid, commission_percentage, commission_amount, payment_status, payment_month)
                VALUES ($tenant_id, '$payment_date', $amount, $commission, $commission_amount, '$status', '$payment_month')";
        
        if ($conn->query($sql) === TRUE) {
            $payment_id = $conn->insert_id;
            
            // Generate receipt
            $receipt_number = "RCP-" . date('Ym', strtotime($payment_date)) . "-" . str_pad($payment_id, 4, '0', STR_PAD_LEFT);
            $receipt_sql = "INSERT INTO receipts (receipt_number, payment_id, tenant_id) VALUES ('$receipt_number', $payment_id, $tenant_id)";
            $conn->query($receipt_sql);
            
            echo "<p style='color:green;'>✓ Added payment: KES " . number_format($amount, 2) . " for tenant ID $tenant_id</p>";
        } else {
            echo "<p style='color:red;'>✗ Error: " . $conn->error . "</p>";
        }
    }
    
    // Show summary
    echo "<h2>Payment Summary:</h2>";
    $result = $conn->query("
        SELECT 
            SUM(amount_paid) as total_collected,
            SUM(commission_amount) as total_commission,
            COUNT(*) as total_payments
        FROM rent_payments WHERE payment_status = 'Paid'
    ");
    $summary = $result->fetch_assoc();
    
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Total Payments</th><th>Total Collected</th><th>Total Commission</th></tr>";
    echo "<tr>";
    echo "<td>" . $summary['total_payments'] . "</td>";
    echo "<td>KES " . number_format($summary['total_collected'], 2) . "</td>";
    echo "<td>KES " . number_format($summary['total_commission'], 2) . "</td>";
    echo "</tr>";
    echo "</table>";
    
    echo "<h2 style='color:green;'>✓ Sample payments added successfully!</h2>";
    echo "<p><a href='test_connection.php'>Back to Test Connection</a></p>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
}
?>

