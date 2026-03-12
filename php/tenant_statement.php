<?php
session_start();
require_once 'config.php';
requireLogin();

if (!isset($_GET['id'])) {
    header('Location: ../html/plots.html');
    exit();
}

$tenant_id = mysqli_real_escape_string($conn, $_GET['id']);

// Get tenant details
$tenant_query = mysqli_query($conn,
    "SELECT t.*, p.plot_name 
     FROM tenants t
     JOIN plots p ON t.plot_id = p.id
     WHERE t.id = $tenant_id"
);
$tenant = mysqli_fetch_assoc($tenant_query);

if (!$tenant) {
    header('Location: ../html/plots.html');
    exit();
}

// Get payment history
$payments = mysqli_query($conn,
    "SELECT * FROM rent_payments 
     WHERE tenant_id = $tenant_id 
     ORDER BY payment_date DESC"
);

$payments_data = [];
$total_paid = 0;
$payment_months = [];

while ($payment = mysqli_fetch_assoc($payments)) {
    if ($payment['payment_status'] == 'Paid') {
        $total_paid += $payment['amount_paid'];
        $payment_months[] = date('Y-m', strtotime($payment['payment_month']));
    }
    
    // Get receipt number
    $receipt_query = mysqli_query($conn, "SELECT receipt_number FROM receipts WHERE payment_id = " . $payment['id']);
    $receipt_number = '-';
    if (mysqli_num_rows($receipt_query) > 0) {
        $receipt = mysqli_fetch_assoc($receipt_query);
        $receipt_number = $receipt['receipt_number'];
    }
    
    $payments_data[] = [
        'payment_date' => date('d/m/Y', strtotime($payment['payment_date'])),
        'amount_paid' => formatMoney($payment['amount_paid']),
        'commission' => formatMoney($payment['commission_amount']),
        'payment_month' => date('F Y', strtotime($payment['payment_month'])),
        'status' => $payment['payment_status'],
        'receipt_number' => $receipt_number
    ];
}

// Calculate months since move in
$move_in = new DateTime($tenant['move_in_date']);
$now = new DateTime();
$interval = $move_in->diff($now);
$total_months = ($interval->y * 12) + $interval->m + 1;

// Calculate expected rent
$expected_rent = $total_months * $tenant['rent_amount'];
$outstanding_balance = $expected_rent - $total_paid;

// Calculate months unpaid
$months_unpaid = 0;
$current_month = date('Y-m');
for ($i = 0; $i < $total_months; $i++) {
    $month = date('Y-m', strtotime("-$i months"));
    if (!in_array($month, $payment_months)) {
        $months_unpaid++;
    }
}

header('Content-Type: application/json');
echo json_encode([
    'tenant' => [
        'id' => $tenant['id'],
        'tenant_id' => $tenant['tenant_id'],
        'tenant_name' => $tenant['tenant_name'],
        'phone_number' => $tenant['phone_number'],
        'plot_id' => $tenant['plot_id'],
        'plot_name' => $tenant['plot_name'],
        'house_number' => $tenant['house_number'],
        'house_type' => $tenant['house_type'],
        'rent_amount' => formatMoney($tenant['rent_amount']),
        'deposit_amount' => formatMoney($tenant['deposit_amount']),
        'deposit_paid' => $tenant['deposit_paid']
    ],
    'total_paid' => formatMoney($total_paid),
    'outstanding_balance' => formatMoney($outstanding_balance),
    'months_unpaid' => $months_unpaid,
    'payments' => $payments_data
]);
exit();
?>
