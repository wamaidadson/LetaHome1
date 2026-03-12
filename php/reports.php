 <?php
session_start();
require_once 'config.php';
requireLogin();

$report_type = isset($_GET['type']) ? $_GET['type'] : 'monthly';

$rows = [];
$footer = [];

// Monthly Rent Report
if ($report_type == 'monthly') {
    $report_query = mysqli_query($conn,
        "SELECT 
            DATE_FORMAT(rp.payment_date, '%M %Y') as month,
            COUNT(DISTINCT rp.tenant_id) as tenants_paid,
            COUNT(rp.id) as total_payments,
            SUM(rp.amount_paid) as total_collected,
            SUM(rp.commission_amount) as total_commission
         FROM rent_payments rp
         WHERE rp.payment_status = 'Paid'
         GROUP BY YEAR(rp.payment_date), MONTH(rp.payment_date)
         ORDER BY rp.payment_date DESC"
    );
    
    while ($row = mysqli_fetch_assoc($report_query)) {
        $rows[] = [
            $row['month'],
            $row['tenants_paid'],
            $row['total_payments'],
            formatMoney($row['total_collected']),
            formatMoney($row['total_commission'])
        ];
    }
}

// Plot Financial Report
if ($report_type == 'plot') {
    $report_query = mysqli_query($conn,
        "SELECT 
            p.plot_name,
            COUNT(DISTINCT t.id) as total_tenants,
            COALESCE(SUM(t.rent_amount), 0) as expected_rent,
            COALESCE(SUM(rp.amount_paid), 0) as collected_rent,
            COALESCE(SUM(rp.commission_amount), 0) as total_commission
         FROM plots p
         LEFT JOIN tenants t ON p.id = t.plot_id AND t.status = 'Active'
         LEFT JOIN rent_payments rp ON t.id = rp.tenant_id AND rp.payment_status = 'Paid'
         GROUP BY p.id
         ORDER BY p.plot_name"
    );
    
    while ($row = mysqli_fetch_assoc($report_query)) {
        $collection_rate = $row['expected_rent'] > 0 ? ($row['collected_rent'] / $row['expected_rent']) * 100 : 0;
        $rows[] = [
            $row['plot_name'],
            $row['total_tenants'],
            formatMoney($row['expected_rent']),
            formatMoney($row['collected_rent']),
            number_format($collection_rate, 1) . '%',
            formatMoney($row['total_commission'])
        ];
    }
}

// Commission Report
if ($report_type == 'commission') {
    $total_commission = 0;
    $report_query = mysqli_query($conn,
        "SELECT 
            DATE_FORMAT(rp.payment_date, '%M %Y') as month,
            t.tenant_name,
            p.plot_name,
            t.house_number,
            rp.amount_paid,
            rp.commission_percentage,
            rp.commission_amount
         FROM rent_payments rp
         JOIN tenants t ON rp.tenant_id = t.id
         JOIN plots p ON t.plot_id = p.id
         WHERE rp.payment_status = 'Paid'
         ORDER BY rp.payment_date DESC"
    );
    
    while ($row = mysqli_fetch_assoc($report_query)) {
        $total_commission += $row['commission_amount'];
        $rows[] = [
            $row['month'],
            $row['tenant_name'],
            $row['plot_name'],
            $row['house_number'],
            formatMoney($row['amount_paid']),
            $row['commission_percentage'] . '%',
            formatMoney($row['commission_amount'])
        ];
    }
    
    $footer = ['', '', '', '', '<strong>Total Commission:</strong>', '<strong>' . formatMoney($total_commission) . '</strong>'];
}

// Arrears Report
if ($report_type == 'arrears') {
    $total_arrears = 0;
    $report_query = mysqli_query($conn,
        "SELECT 
            t.tenant_id,
            t.tenant_name,
            p.plot_name,
            t.house_number,
            t.rent_amount,
            COUNT(CASE WHEN rp.payment_status = 'Not Paid' OR rp.id IS NULL THEN 1 END) as months_unpaid,
            (t.rent_amount * COUNT(CASE WHEN rp.payment_status = 'Not Paid' OR rp.id IS NULL THEN 1 END)) as outstanding_balance
         FROM tenants t
         JOIN plots p ON t.plot_id = p.id
         LEFT JOIN rent_payments rp ON t.id = rp.tenant_id 
         WHERE t.status = 'Active'
         GROUP BY t.id
         HAVING months_unpaid > 0
         ORDER BY outstanding_balance DESC"
    );
    
    while ($row = mysqli_fetch_assoc($report_query)) {
        $total_arrears += $row['outstanding_balance'];
        $rows[] = [
            $row['tenant_id'],
            $row['tenant_name'],
            $row['plot_name'],
            $row['house_number'],
            formatMoney($row['rent_amount']),
            $row['months_unpaid'],
            formatMoney($row['outstanding_balance'])
        ];
    }
    
    $footer = ['', '', '', '', '<strong>Total Arrears:</strong>', '<strong>' . formatMoney($total_arrears) . '</strong>'];
}

header('Content-Type: application/json');
echo json_encode([
    'rows' => $rows,
    'footer' => $footer
]);
exit();
?>
