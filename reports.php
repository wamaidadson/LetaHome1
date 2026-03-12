<?php
require_once 'config.php';
requireLogin();

$report_type = isset($_GET['type']) ? $_GET['type'] : 'monthly';
$month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

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
}

// Commission Report
if ($report_type == 'commission') {
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
}

// Arrears Report
if ($report_type == 'arrears') {
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
}

$page_title = 'Reports - Leta Homes Agency';
?>
<?php include 'header.php'; ?>
    
    <div class="layout-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="container-fluid py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-chart-bar"></i> Reports</h2>
            </div>
            
            <!-- Report Type Selector -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="table-container">
                        <div class="btn-group" role="group">
                            <a href="?type=monthly" class="btn <?php echo $report_type == 'monthly' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                Monthly Rent Report
                            </a>
                            <a href="?type=plot" class="btn <?php echo $report_type == 'plot' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                Plot Financial Report
                            </a>
                            <a href="?type=commission" class="btn <?php echo $report_type == 'commission' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                Commission Report
                            </a>
                            <a href="?type=arrears" class="btn <?php echo $report_type == 'arrears' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                Arrears Report
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Report Display -->
            <div class="table-container">
                <div class="table-header">
                    <h5>
                        <?php 
                        switch($report_type) {
                            case 'monthly':
                                echo 'Monthly Rent Collection Report';
                                break;
                            case 'plot':
                                echo 'Plot Financial Report';
                                break;
                            case 'commission':
                                echo 'Commission Report';
                                break;
                            case 'arrears':
                                echo 'Rent Arrears Report';
                                break;
                        }
                        ?>
                    </h5>
                    <button onclick="window.print()" class="btn btn-primary btn-sm">
                        <i class="fas fa-print"></i> Print Report
                    </button>
                </div>
                
                <?php if ($report_type == 'monthly'): ?>
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Tenants Paid</th>
                                <th>Total Payments</th>
                                <th>Total Collected</th>
                                <th>Total Commission</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($report_query)): ?>
                            <tr>
                                <td><?php echo $row['month']; ?></td>
                                <td><?php echo $row['tenants_paid']; ?></td>
                                <td><?php echo $row['total_payments']; ?></td>
                                <td><?php echo formatMoney($row['total_collected']); ?></td>
                                <td><?php echo formatMoney($row['total_commission']); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
                
                <?php if ($report_type == 'plot'): ?>
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th>Plot Name</th>
                                <th>Total Tenants</th>
                                <th>Expected Rent</th>
                                <th>Collected Rent</th>
                                <th>Collection Rate</th>
                                <th>Total Commission</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($report_query)): ?>
                            <?php $collection_rate = $row['expected_rent'] > 0 ? ($row['collected_rent'] / $row['expected_rent']) * 100 : 0; ?>
                            <tr>
                                <td><?php echo $row['plot_name']; ?></td>
                                <td><?php echo $row['total_tenants']; ?></td>
                                <td><?php echo formatMoney($row['expected_rent']); ?></td>
                                <td><?php echo formatMoney($row['collected_rent']); ?></td>
                                <td>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" style="width: <?php echo $collection_rate; ?>%">
                                            <?php echo number_format($collection_rate, 1); ?>%
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo formatMoney($row['total_commission']); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
                
                <?php if ($report_type == 'commission'): ?>
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Tenant</th>
                                <th>Plot</th>
                                <th>House</th>
                                <th>Rent Paid</th>
                                <th>Commission %</th>
                                <th>Commission Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($report_query)): ?>
                            <tr>
                                <td><?php echo $row['month']; ?></td>
                                <td><?php echo $row['tenant_name']; ?></td>
                                <td><?php echo $row['plot_name']; ?></td>
                                <td><?php echo $row['house_number']; ?></td>
                                <td><?php echo formatMoney($row['amount_paid']); ?></td>
                                <td><?php echo $row['commission_percentage']; ?>%</td>
                                <td><?php echo formatMoney($row['commission_amount']); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                        <tfoot>
                            <?php
                            mysqli_data_seek($report_query, 0);
                            $total_commission = 0;
                            while ($row = mysqli_fetch_assoc($report_query)) {
                                $total_commission += $row['commission_amount'];
                            }
                            ?>
                            <tr class="table-info">
                                <td colspan="6" class="text-end"><strong>Total Commission:</strong></td>
                                <td><strong><?php echo formatMoney($total_commission); ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                <?php endif; ?>
                
                <?php if ($report_type == 'arrears'): ?>
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th>Tenant ID</th>
                                <th>Tenant Name</th>
                                <th>Plot</th>
                                <th>House</th>
                                <th>Monthly Rent</th>
                                <th>Months Unpaid</th>
                                <th>Outstanding Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total_arrears = 0;
                            while ($row = mysqli_fetch_assoc($report_query)): 
                                $total_arrears += $row['outstanding_balance'];
                            ?>
                            <tr>
                                <td><?php echo $row['tenant_id']; ?></td>
                                <td><?php echo $row['tenant_name']; ?></td>
                                <td><?php echo $row['plot_name']; ?></td>
                                <td><?php echo $row['house_number']; ?></td>
                                <td><?php echo formatMoney($row['rent_amount']); ?></td>
                                <td><span class="badge badge-danger"><?php echo $row['months_unpaid']; ?></span></td>
                                <td><strong><?php echo formatMoney($row['outstanding_balance']); ?></strong></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-danger">
                                <td colspan="6" class="text-end"><strong>Total Arrears:</strong></td>
                                <td><strong><?php echo formatMoney($total_arrears); ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>

