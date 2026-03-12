<?php
session_start();
require_once 'config.php';
requireLogin();

$search_term = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$search_results = [];

if (!empty($search_term)) {
    // Search tenants
    $search_query = mysqli_query($conn,
        "SELECT t.*, p.plot_name,
         'Tenant' as result_type
         FROM tenants t
         JOIN plots p ON t.plot_id = p.id
         WHERE t.tenant_name LIKE '%$search_term%'
            OR t.tenant_id LIKE '%$search_term%'
            OR t.house_number LIKE '%$search_term%'
            OR t.phone_number LIKE '%$search_term%'
         UNION
         SELECT t.*, p.plot_name,
         'Payment' as result_type
         FROM rent_payments rp
         JOIN tenants t ON rp.tenant_id = t.id
         JOIN plots p ON t.plot_id = p.id
         WHERE DATE_FORMAT(rp.payment_date, '%d/%m/%Y') LIKE '%$search_term%'
            OR rp.amount_paid LIKE '%$search_term%'
         UNION
         SELECT t.*, p.plot_name,
         'Receipt' as result_type
         FROM receipts r
         JOIN rent_payments rp ON r.payment_id = rp.id
         JOIN tenants t ON rp.tenant_id = t.id
         JOIN plots p ON t.plot_id = p.id
         WHERE r.receipt_number LIKE '%$search_term%'
         ORDER BY result_type"
    );
    
    while ($result = mysqli_fetch_assoc($search_query)) {
        $search_results[] = [
            'id' => $result['id'],
            'tenant_id' => $result['tenant_id'],
            'tenant_name' => $result['tenant_name'],
            'plot_name' => $result['plot_name'],
            'house_number' => $result['house_number'],
            'result_type' => $result['result_type']
        ];
    }
}

header('Content-Type: application/json');
echo json_encode([
    'results' => $search_results
]);
exit();
?>
