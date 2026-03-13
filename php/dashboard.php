<?php
session_start();
require_once 'config.php';
// requireLogin(); // Bypassed for admin direct access

// Get dashboard statistics
$total_plots = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM plots"))['count'];
$total_tenants = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM tenants WHERE status='Active'"))['count'];

// Total rent collected this month
$current_month = date('Y-m-01');
$rent_collected = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COALESCE(SUM(amount_paid), 0) as total FROM rent_payments 
     WHERE payment_status='Paid' AND payment_month = '$current_month'"
))['total'];

// Total unpaid rent
$unpaid_rent = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COALESCE(SUM(rent_amount), 0) as total FROM tenants t 
     LEFT JOIN rent_payments rp ON t.id = rp.tenant_id AND rp.payment_month = '$current_month'
     WHERE t.status='Active' AND (rp.payment_status != 'Paid' OR rp.id IS NULL)"
))['total'];

// Total commission earned this month
$commission_earned = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COALESCE(SUM(commission_amount), 0) as total FROM rent_payments 
     WHERE payment_status='Paid' AND payment_month = '$current_month'"
))['total'];

// Tenants in arrears
$arrears_query = "SELECT COUNT(DISTINCT t.id) as count, 
                  COALESCE(SUM(t.rent_amount - COALESCE(rp.amount_paid, 0)), 0) as total_arrears
                  FROM tenants t
                  LEFT JOIN rent_payments rp ON t.id = rp.tenant_id 
                  WHERE t.status='Active'
                  GROUP BY t.id
                  HAVING COALESCE(SUM(t.rent_amount - COALESCE(rp.amount_paid, 0)), 0) > 0";
$arrears_result = mysqli_query($conn, $arrears_query);
$tenants_in_arrears = mysqli_num_rows($arrears_result);
$total_arrears = 0;
while ($row = mysqli_fetch_assoc($arrears_result)) {
    $total_arrears += $row['total_arrears'];
}

// Total deposits from all tenants
$total_deposits = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COALESCE(SUM(deposit_amount), 0) as total FROM tenants WHERE status='Active'"
))['total'];

// Monthly rent collection summary
$monthly_summary = mysqli_query($conn, 
    "SELECT DATE_FORMAT(payment_date, '%M %Y') as month,
            COUNT(DISTINCT tenant_id) as tenants_paid,
            SUM(amount_paid) as total_collected
     FROM rent_payments 
     WHERE payment_status='Paid'
     GROUP BY YEAR(payment_date), MONTH(payment_date)
     ORDER BY payment_date DESC
     LIMIT 6"
);

$monthly_summary_data = [];
while ($row = mysqli_fetch_assoc($monthly_summary)) {
    $monthly_summary_data[] = [
        'month' => $row['month'],
        'tenants_paid' => $row['tenants_paid'],
        'total_collected' => formatMoney($row['total_collected'])
    ];
}

// Recent tenant payments
$recent_payments = mysqli_query($conn,
    "SELECT rp.*, t.tenant_name, t.house_number, p.plot_name
     FROM rent_payments rp
     JOIN tenants t ON rp.tenant_id = t.id
     JOIN plots p ON t.plot_id = p.id
     WHERE rp.payment_status='Paid'
     ORDER BY rp.payment_date DESC
     LIMIT 10"
);

$recent_payments_data = [];
while ($row = mysqli_fetch_assoc($recent_payments)) {
    $recent_payments_data[] = [
        'payment_date' => date('d/m/Y', strtotime($row['payment_date'])),
        'tenant_name' => $row['tenant_name'],
        'plot_name' => $row['plot_name'],
        'amount_paid' => formatMoney($row['amount_paid'])
    ];
}

// Rent reminders (tenants who haven't paid this month)
$reminders_query = "SELECT t.*, p.plot_name,
                   DATEDIFF(CURDATE(), DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) as days_overdue
                   FROM tenants t
                   JOIN plots p ON t.plot_id = p.id
                   LEFT JOIN rent_payments rp ON t.id = rp.tenant_id 
                       AND rp.payment_month = DATE_FORMAT(CURDATE(), '%Y-%m-01')
                   WHERE t.status='Active' 
                       AND (rp.id IS NULL OR rp.payment_status != 'Paid')
                   ORDER BY t.tenant_name";
$reminders = mysqli_query($conn, $reminders_query);
$reminder_count = mysqli_num_rows($reminders);

$reminders_data = [];
while ($row = mysqli_fetch_assoc($reminders)) {
    $reminders_data[] = [
        'id' => $row['id'],
        'tenant_id' => $row['tenant_id'],
        'tenant_name' => $row['tenant_name'],
        'plot_name' => $row['plot_name'],
        'house_number' => $row['house_number'],
        'rent_amount' => formatMoney($row['rent_amount']),
        'days_overdue' => $row['days_overdue']
    ];
}

header('Content-Type: application/json');
echo json_encode([
    'stats' => [
        'total_plots' => $total_plots,
        'total_tenants' => $total_tenants,
        'rent_collected' => formatMoney($rent_collected),
        'unpaid_rent' => formatMoney($unpaid_rent),
        'commission_earned' => formatMoney($commission_earned),
        'tenants_in_arrears' => $tenants_in_arrears,
        'total_deposits' => formatMoney($total_deposits),
        'total_arrears' => formatMoney($total_arrears)
    ],
    'monthly_summary' => $monthly_summary_data,
    'recent_payments' => $recent_payments_data,
    'reminders' => $reminders_data,
    'reminder_count' => $reminder_count
]);
exit();
?>
