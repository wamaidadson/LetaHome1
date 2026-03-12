<?php
session_start();
require_once 'config.php';
requireLogin();

// Handle GET request - return plots list
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action']) && $_GET['action'] == 'plots') {
    $plots = mysqli_query($conn, "SELECT id, plot_name FROM plots ORDER BY plot_name");
    $plots_data = [];
    while ($row = mysqli_fetch_assoc($plots)) {
        $plots_data[] = $row;
    }
    
    header('Content-Type: application/json');
    echo json_encode(['plots' => $plots_data]);
    exit();
}

// Handle POST request - add new tenant
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tenant_id = generateTenantID($conn);
    $plot_id = mysqli_real_escape_string($conn, $_POST['plot_id']);
    $tenant_name = mysqli_real_escape_string($conn, $_POST['tenant_name']);
    $phone_number = mysqli_real_escape_string($conn, $_POST['phone_number']);
    $house_number = mysqli_real_escape_string($conn, $_POST['house_number']);
    $house_type = mysqli_real_escape_string($conn, $_POST['house_type']);
    $rent_amount = mysqli_real_escape_string($conn, $_POST['rent_amount']);
    $commission_percentage = mysqli_real_escape_string($conn, $_POST['commission_percentage']);
    $deposit_amount = mysqli_real_escape_string($conn, $_POST['deposit_amount']);
    $deposit_paid = mysqli_real_escape_string($conn, $_POST['deposit_paid']);
    $move_in_date = mysqli_real_escape_string($conn, $_POST['move_in_date']);
    
    // Set deposit date if paid
    $deposit_date = $deposit_paid == 'Yes' ? date('Y-m-d') : null;
    
    $query = "INSERT INTO tenants (tenant_id, plot_id, tenant_name, phone_number, house_number, house_type, 
                                   rent_amount, commission_percentage, deposit_amount, deposit_paid, deposit_date, move_in_date, status) 
              VALUES ('$tenant_id', '$plot_id', '$tenant_name', '$phone_number', '$house_number', '$house_type', 
                      '$rent_amount', '$commission_percentage', '$deposit_amount', '$deposit_paid', " . 
                      ($deposit_date ? "'$deposit_date'" : "NULL") . ", '$move_in_date', 'Active')";
    
    if (mysqli_query($conn, $query)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Tenant added successfully']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error: ' . mysqli_error($conn)]);
    }
    exit();
}
?>
