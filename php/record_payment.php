<?php
session_start();
require_once 'config.php';
requireLogin();

// Handle GET request - return tenant data
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['tenant_id'])) {
    $tenant_id = mysqli_real_escape_string($conn, $_GET['tenant_id']);
    
    $tenant_query = mysqli_query($conn, 
        "SELECT t.*, p.plot_name 
         FROM tenants t
         JOIN plots p ON t.plot_id = p.id
         WHERE t.id = $tenant_id"
    );
    
    $tenant = mysqli_fetch_assoc($tenant_query);
    
    if (!$tenant) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Tenant not found']);
        exit();
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'tenant' => [
            'id' => $tenant['id'],
            'tenant_id' => $tenant['tenant_id'],
            'tenant_name' => $tenant['tenant_name'],
            'plot_id' => $tenant['plot_id'],
            'plot_name' => $tenant['plot_name'],
            'house_number' => $tenant['house_number'],
            'rent_amount' => formatMoney($tenant['rent_amount']),
            'rent_amount_raw' => $tenant['rent_amount'],
            'commission_percentage' => $tenant['commission_percentage']
        ]
    ]);
    exit();
}

// Handle POST request - record payment
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tenant_id = mysqli_real_escape_string($conn, $_POST['tenant_id']);
    $payment_date = mysqli_real_escape_string($conn, $_POST['payment_date']);
    $amount_paid = mysqli_real_escape_string($conn, $_POST['amount_paid']);
    
    // Get tenant commission percentage
    $tenant_query = mysqli_query($conn, "SELECT commission_percentage FROM tenants WHERE id = $tenant_id");
    $tenant = mysqli_fetch_assoc($tenant_query);
    $commission_percentage = $tenant['commission_percentage'];
    $commission_amount = ($amount_paid * $commission_percentage) / 100;
    $payment_month = date('Y-m-01', strtotime($payment_date));
    
    // Insert payment
    $query = "INSERT INTO rent_payments (tenant_id, payment_date, amount_paid, commission_percentage, 
                                         commission_amount, payment_status, payment_month) 
              VALUES ('$tenant_id', '$payment_date', '$amount_paid', '$commission_percentage', 
                      '$commission_amount', 'Paid', '$payment_month')";
    
    if (mysqli_query($conn, $query)) {
        $payment_id = mysqli_insert_id($conn);
        
        // Generate receipt
        $receipt_number = generateReceiptNumber($conn);
        mysqli_query($conn, "INSERT INTO receipts (receipt_number, payment_id, tenant_id) 
                            VALUES ('$receipt_number', '$payment_id', '$tenant_id')");
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'payment_id' => $payment_id]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error: ' . mysqli_error($conn)]);
    }
    exit();
}
?>
