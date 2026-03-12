<?php
// This script adds deposit columns to the tenants table

$conn = new mysqli("localhost", "root", "", "leta_homes");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if columns already exist
$result = $conn->query("SHOW COLUMNS FROM tenants LIKE 'deposit_amount'");

if ($result->num_rows == 0) {
    // Add deposit_amount column
    $sql1 = "ALTER TABLE tenants ADD COLUMN deposit_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00 AFTER commission_percentage";
    if ($conn->query($sql1)) {
        echo "Added deposit_amount column<br>";
    } else {
        echo "Error adding deposit_amount: " . $conn->error . "<br>";
    }
} else {
    echo "deposit_amount column already exists<br>";
}

// Check if deposit_paid column exists
$result = $conn->query("SHOW COLUMNS FROM tenants LIKE 'deposit_paid'");

if ($result->num_rows == 0) {
    // Add deposit_paid column
    $sql2 = "ALTER TABLE tenants ADD COLUMN deposit_paid ENUM('Yes', 'No') DEFAULT 'No' AFTER deposit_amount";
    if ($conn->query($sql2)) {
        echo "Added deposit_paid column<br>";
    } else {
        echo "Error adding deposit_paid: " . $conn->error . "<br>";
    }
} else {
    echo "deposit_paid column already exists<br>";
}

// Check if deposit_date column exists
$result = $conn->query("SHOW COLUMNS FROM tenants LIKE 'deposit_date'");

if ($result->num_rows == 0) {
    // Add deposit_date column
    $sql3 = "ALTER TABLE tenants ADD COLUMN deposit_date DATE AFTER deposit_paid";
    if ($conn->query($sql3)) {
        echo "Added deposit_date column<br>";
    } else {
        echo "Error adding deposit_date: " . $conn->error . "<br>";
    }
} else {
    echo "deposit_date column already exists<br>";
}

echo "Database update complete!";
$conn->close();
?>

