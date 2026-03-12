<?php
/**
 * Database Setup Script
 * Run this file to create the database and all required tables
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Leta Homes Agency - Database Setup</h1>";

$servername = "localhost";
$username = "root";
$password = "";
$port = "3306";

// First, connect without database to create it
try {
    $conn = new mysqli($servername, $username, $password, "", $port);
    if ($conn->connect_error) {
        die("<p style='color:red;'>Connection failed: " . $conn->connect_error . "</p>");
    }
    echo "<p style='color:green;'>✓ Connected to MySQL server successfully</p>";
} catch (Exception $e) {
    die("<p style='color:red;'>Failed to connect: " . $e->getMessage() . "</p>");
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS leta_homes";
if ($conn->query($sql) === TRUE) {
    echo "<p style='color:green;'>✓ Database 'leta_homes' created or already exists</p>";
} else {
    echo "<p style='color:red;'>Error creating database: " . $conn->error . "</p>";
}

// Select the database
$conn->select_db("leta_homes");

// =====================================================
// Create tables
// =====================================================

$tables = [
    "users" => "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id VARCHAR(20) UNIQUE NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_username (username),
        INDEX idx_user_id (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "plots" => "CREATE TABLE IF NOT EXISTS plots (
        id INT AUTO_INCREMENT PRIMARY KEY,
        plot_name VARCHAR(100) NOT NULL,
        location VARCHAR(200),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_plot_name (plot_name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "tenants" => "CREATE TABLE IF NOT EXISTS tenants (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tenant_id VARCHAR(20) UNIQUE NOT NULL,
        plot_id INT NOT NULL,
        tenant_name VARCHAR(100) NOT NULL,
        phone_number VARCHAR(20),
        house_number VARCHAR(20),
        house_type VARCHAR(50),
        rent_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        commission_percentage DECIMAL(5, 2) NOT NULL DEFAULT 10.00,
        move_in_date DATE,
        status ENUM('Active', 'Inactive', 'Moved Out') DEFAULT 'Active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (plot_id) REFERENCES plots(id) ON DELETE CASCADE,
        INDEX idx_tenant_id (tenant_id),
        INDEX idx_plot_id (plot_id),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "rent_payments" => "CREATE TABLE IF NOT EXISTS rent_payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT NOT NULL,
        payment_date DATE NOT NULL,
        amount_paid DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        commission_percentage DECIMAL(5, 2) NOT NULL DEFAULT 10.00,
        commission_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        payment_status ENUM('Pending', 'Paid', 'Partial', 'Cancelled') DEFAULT 'Paid',
        payment_month DATE NOT NULL,
        notes TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
        INDEX idx_tenant_id (tenant_id),
        INDEX idx_payment_date (payment_date),
        INDEX idx_payment_month (payment_month),
        INDEX idx_payment_status (payment_status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "receipts" => "CREATE TABLE IF NOT EXISTS receipts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        receipt_number VARCHAR(30) UNIQUE NOT NULL,
        payment_id INT NOT NULL,
        tenant_id INT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (payment_id) REFERENCES rent_payments(id) ON DELETE CASCADE,
        FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
        INDEX idx_receipt_number (receipt_number),
        INDEX idx_payment_id (payment_id),
        INDEX idx_tenant_id (tenant_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

foreach ($tables as $tableName => $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color:green;'>✓ Table '$tableName' created successfully</p>";
    } else {
        echo "<p style='color:red;'>Error creating table '$tableName': " . $conn->error . "</p>";
    }
}

// =====================================================
// Insert default admin user
// =====================================================
$adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
$sql = "INSERT INTO users (user_id, full_name, username, email, password, created_at)
        SELECT 'USR-2026-0001', 'Administrator', 'admin', 'admin@leta.com', '$adminPassword', NOW()
        FROM DUAL
        WHERE NOT EXISTS (SELECT username FROM users WHERE username = 'admin')";

if ($conn->query($sql) === TRUE) {
    if ($conn->affected_rows > 0) {
        echo "<p style='color:green;'>✓ Default admin user created (username: admin, password: admin123)</p>";
    } else {
        echo "<p style='color:orange;'>✓ Admin user already exists</p>";
    }
} else {
    echo "<p style='color:red;'>Error creating admin user: " . $conn->error . "</p>";
}

// =====================================================
// Insert sample data
// =====================================================

// Sample plots
$sql = "INSERT INTO plots (plot_name, location) VALUES 
        ('Sunrise Apartments', 'Kenyatta Avenue, Nairobi'),
        ('Green Valley Heights', 'Mombasa Road, Nairobi'),
        ('Riverside Gardens', 'Kiserian Road, Nairobi'),
        ('Metro Plaza', 'Central Business District, Nairobi')
        ON DUPLICATE KEY UPDATE location = VALUES(location)";

if ($conn->query($sql) === TRUE) {
    echo "<p style='color:green;'>✓ Sample plots added</p>";
} else {
    echo "<p style='color:red;'>Error adding plots: " . $conn->error . "</p>";
}

// =====================================================
// Verify setup
// =====================================================
echo "<hr>";
echo "<h2>Database Setup Complete!</h2>";
echo "<h3>Tables created:</h3>";
$result = $conn->query("SHOW TABLES");
echo "<ul>";
while ($row = $result->fetch_array()) {
    echo "<li>" . $row[0] . "</li>";
}
echo "</ul>";

echo "<h3>Database Information:</h3>";
echo "<ul>";
echo "<li><strong>Database Name:</strong> leta_homes</li>";
echo "<li><strong>Host:</strong> localhost</li>";
echo "<li><strong>Port:</strong> 3306</li>";
echo "<li><strong>Username:</strong> root</li>";
echo "<li><strong>Password:</strong> (empty)</li>";
echo "</ul>";

echo "<h3>Default Login:</h3>";
echo "<ul>";
echo "<li><strong>Username:</strong> admin</li>";
echo "<li><strong>Password:</strong> admin123</li>";
echo "</ul>";

echo "<p style='background:#e0f7fa; padding:15px; border-radius:5px;'>";
echo "<strong>Next Steps:</strong><br>";
echo "1. Make sure XAMPP Apache and MySQL services are running<br>";
echo "2. Open your browser and navigate to: <a href='http://localhost/leta_homes_agency/'>http://localhost/leta_homes_agency/</a><br>";
echo "3. Login with the default admin credentials";
echo "</p>";

$conn->close();
?>

