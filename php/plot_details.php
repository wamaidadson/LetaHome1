<?php
session_start();
require_once 'config.php';
requireLogin();

if (!isset($_GET['id'])) {
    header('Location: ../html/plots.html');
    exit();
}

$plot_id = mysqli_real_escape_string($conn, $_GET['id']);

// Get plot details
$plot_query = mysqli_query($conn, "SELECT * FROM plots WHERE id = $plot_id");
$plot = mysqli_fetch_assoc($plot_query);

if (!$plot) {
    header('Location: ../html/plots.html');
    exit();
}

// Get tenants in this plot
$tenants = mysqli_query($conn, 
    "SELECT t.*, 
     (SELECT COUNT(*) FROM rent_payments WHERE tenant_id = t.id AND payment_status='Paid') as payments_count,
     (SELECT COALESCE(SUM(amount_paid), 0) FROM rent_payments WHERE tenant_id = t.id) as total_paid
     FROM tenants t
     WHERE t.plot_id = $plot_id AND t.status='Active'
     ORDER BY t.tenant_name"
);

$tenants_data = [];
while ($tenant = mysqli_fetch_assoc($tenants)) {
    $tenants_data[] = [
        'id' => $tenant['id'],
        'tenant_id' => $tenant['tenant_id'],
        'tenant_name' => $tenant['tenant_name'],
        'house_number' => $tenant['house_number'],
        'house_type' => $tenant['house_type'],
        'rent_amount' => formatMoney($tenant['rent_amount']),
        'deposit_amount' => formatMoney($tenant['deposit_amount']),
        'deposit_paid' => $tenant['deposit_paid'],
        'commission_percentage' => $tenant['commission_percentage'],
        'move_in_date' => date('d/m/Y', strtotime($tenant['move_in_date'])),
        'status' => $tenant['status']
    ];
}

// Get financial summary
$financial_summary = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT 
        COUNT(DISTINCT t.id) as total_tenants,
        COALESCE(SUM(t.rent_amount), 0) as total_expected_rent,
        COALESCE(SUM(rp.amount_paid), 0) as total_rent_collected,
        COALESCE(SUM(rp.commission_amount), 0) as total_commission
     FROM tenants t
     LEFT JOIN rent_payments rp ON t.id = rp.tenant_id AND rp.payment_status='Paid'
     WHERE t.plot_id = $plot_id AND t.status='Active'"
));

$unpaid_rent = $financial_summary['total_expected_rent'] - $financial_summary['total_rent_collected'];

header('Content-Type: application/json');
echo json_encode([
    'plot' => [
        'id' => $plot['id'],
        'plot_name' => $plot['plot_name'],
        'location' => $plot['location'],
        'created_date' => date('d/m/Y', strtotime($plot['created_at']))
    ],
    'financial_summary' => [
        'total_tenants' => $financial_summary['total_tenants'],
        'total_rent_collected' => formatMoney($financial_summary['total_rent_collected']),
        'total_unpaid' => formatMoney($unpaid_rent),
        'total_commission' => formatMoney($financial_summary['total_commission'])
    ],
    'tenants' => $tenants_data
]);
exit();
?>
