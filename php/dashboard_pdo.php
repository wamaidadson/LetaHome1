<?php
session_start();
require_once 'config.php';
// requireLogin(); // Bypassed for admin direct access

$pdo = getDatabaseConnection();

// Get dashboard statistics
$stmt = $pdo->query("SELECT COUNT(*) as count FROM plots");
$total_plots = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM tenants WHERE status='Active'");
$total_tenants = $stmt->fetch()['count'];

// Total rent collected this month
$current_month = date('Y-m-01');
$stmt = $pdo->query("SELECT COALESCE(SUM(amount_paid), 0) as total FROM rent_payments WHERE payment_status='Paid' AND payment_month = '$current_month'");
$rent_collected = $stmt->fetch()['total'];

// Total unpaid rent
$stmt = $pdo->query("SELECT COALESCE(SUM(rent_amount), 0) as total FROM tenants t 
     LEFT JOIN rent_payments rp ON t.id = rp.tenant_id AND rp.payment_month = '$current_month'
     WHERE t.status='Active' AND (rp.payment_status != 'Paid' OR rp.id IS NULL)");
$unpaid_rent = $stmt->fetch()['total'];

// Total commission earned this month
$stmt = $pdo->query("SELECT COALESCE(SUM(commission_amount), 0) as total FROM rent_payments WHERE payment_status='Paid' AND payment_month = '$current_month'");
$commission_earned = $stmt->fetch()['total'];

// Tenants in arrears
$stmt = $pdo->query("SELECT COUNT(DISTINCT t.id) as count FROM tenants t
  LEFT JOIN rent_payments rp ON t.id = rp.tenant_id 
  WHERE t.status='Active'
  GROUP BY t.id
  HAVING COALESCE(SUM(t.rent_amount - COALESCE(rp.amount_paid, 0)), 0) > 0");
$tenants_in_arrears = $stmt->rowCount();
$total_arrears = 0;

// Total deposits
$stmt = $pdo->query("SELECT COALESCE(SUM(deposit_amount), 0) as total FROM tenants WHERE status='Active'");
$total_deposits = $stmt->fetch()['total'];

// Monthly summary
$stmt = $pdo->query("SELECT TO_CHAR(payment_date, 'Mon YYYY') as month,
            COUNT(DISTINCT tenant_id) as tenants_paid,
            SUM(amount_paid) as total_collected
     FROM rent_payments 
     WHERE payment_status='Paid'
     GROUP BY DATE_TRUNC('month', payment_date)
     ORDER BY payment_date DESC
     LIMIT 6");
$monthly_summary_data = $stmt->fetchAll();
foreach ($monthly_summary_data as &$item) {
    $item['total_collected'] = formatMoney($item['total_collected']);
}

// Recent payments
$stmt = $pdo->query("SELECT rp.*, t.tenant_name, t.house_number, p.plot_name
     FROM rent_payments rp
     JOIN tenants t ON rp.tenant_id = t.id
     JOIN plots p ON t.plot_id = p.id
     WHERE rp.payment_status='Paid'
     ORDER BY rp.payment_date DESC
     LIMIT 10");
$recent_payments_data = $stmt->fetchAll();
foreach ($recent_payments_data as &$item) {
    $item['payment_date'] = date('d/m/Y', strtotime($item['payment_date']));
    $item['amount_paid'] = formatMoney($item['amount_paid']);
}

// Reminders
$stmt = $pdo->query("SELECT t.*, p.plot_name
                   FROM tenants t
                   JOIN plots p ON t.plot_id = p.id
                   LEFT JOIN rent_payments rp ON t.id = rp.tenant_id 
                       AND rp.payment_month = DATE_TRUNC('month', NOW())
                   WHERE t.status='Active' AND (rp.id IS NULL OR rp.payment_status != 'Paid')
                   ORDER BY t.tenant_name");
$reminders_data = $stmt->fetchAll();
foreach ($reminders_data as &$item) {
    $item['rent_amount'] = formatMoney($item['rent_amount']);
}
$reminder_count = count($reminders_data);

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

