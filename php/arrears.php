<?php
session_start();
require_once 'config.php';
requireLogin();

// Get all tenants with arrears
$arrears_query = "SELECT t.*, p.plot_name,
                  COUNT(CASE WHEN rp.payment_status = 'Not Paid' OR rp.id IS NULL THEN 1 END) as months_unpaid,
                  (t.rent_amount * COUNT(CASE WHEN rp.payment_status = 'Not Paid' OR rp.id IS NULL THEN 1 END)) as outstanding_balance
                  FROM tenants t
                  JOIN plots p ON t.plot_id = p.id
                  LEFT JOIN rent_payments rp ON t.id = rp.tenant_id 
                  WHERE t.status = 'Active'
                  GROUP BY t.id
                  HAVING months_unpaid > 0
                  ORDER BY outstanding_balance DESC";

$tenants_in_arrears = mysqli_query($conn, $arrears_query);

$tenants_data = [];
$total_arrears = 0;

while ($tenant = mysqli_fetch_assoc($tenants_in_arrears)) {
    $tenants_data[] = [
        'id' => $tenant['id'],
        'tenant_id' => $tenant['tenant_id'],
        'tenant_name' => $tenant['tenant_name'],
        'plot_name' => $tenant['plot_name'],
        'house_number' => $tenant['house_number'],
        'rent_amount' => formatMoney($tenant['rent_amount']),
        'months_unpaid' => $tenant['months_unpaid'],
        'outstanding_balance' => formatMoney($tenant['outstanding_balance'])
    ];
    $total_arrears += $tenant['outstanding_balance'];
}

header('Content-Type: application/json');
echo json_encode([
    'tenants_count' => count($tenants_data),
    'total_arrears' => formatMoney($total_arrears),
    'tenants' => $tenants_data
]);
exit();
?>
