<?php
require_once('config.php');
requireLogin();

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

$page_title = 'Dashboard - Leta Homes Agency';
?>
<?php include 'header.php'; ?>
    
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="container-fluid py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-dashboard"></i> Dashboard</h2>
                <div>
                    <span class="text-muted">Welcome, <?php echo $_SESSION['full_name']; ?></span>
                </div>
            </div>
            
            <?php if ($reminder_count > 0): ?>
            <div class="alert-reminder">
                <i class="fas fa-bell"></i>
                <strong>Reminder:</strong> <?php echo $reminder_count; ?> tenant(s) have not paid rent for the current month.
            </div>
            <?php endif; ?>
            
            <!-- Statistics Cards -->
            <div class="dashboard-container">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="card-label">Total Plots</div>
                            <div class="card-value"><?php echo $total_plots; ?></div>
                        </div>
                        <div class="card-icon" style="background: #3498db;">
                            <i class="fas fa-building"></i>
                        </div>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="card-label">Total Tenants</div>
                            <div class="card-value"><?php echo $total_tenants; ?></div>
                        </div>
                        <div class="card-icon" style="background: #27ae60;">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="card-label">Rent Collected</div>
                            <div class="card-value"><?php echo formatMoney($rent_collected); ?></div>
                        </div>
                        <div class="card-icon" style="background: #f39c12;">
                            <i class="fas fa-money-bill"></i>
                        </div>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="card-label">Unpaid Rent</div>
                            <div class="card-value"><?php echo formatMoney($unpaid_rent); ?></div>
                        </div>
                        <div class="card-icon" style="background: #e74c3c;">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="card-label">Commission Earned</div>
                            <div class="card-value"><?php echo formatMoney($commission_earned); ?></div>
                        </div>
                        <div class="card-icon" style="background: #9b59b6;">
                            <i class="fas fa-percent"></i>
                        </div>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="card-label">Tenants in Arrears</div>
                            <div class="card-value"><?php echo $tenants_in_arrears; ?></div>
                        </div>
                        <div class="card-icon" style="background: #e67e22;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="card-label">Total Arrears</div>
                            <div class="card-value"><?php echo formatMoney($total_arrears); ?></div>
                        </div>
                        <div class="card-icon" style="background: #c0392b;">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Monthly Collection Summary -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="table-container">
                        <div class="table-header">
                            <h5>Monthly Rent Collection Summary</h5>
                        </div>
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Tenants Paid</th>
                                    <th>Total Collected</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($monthly_summary)): ?>
                                <tr>
                                    <td><?php echo $row['month']; ?></td>
                                    <td><?php echo $row['tenants_paid']; ?></td>
                                    <td><?php echo formatMoney($row['total_collected']); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Recent Payments -->
                <div class="col-md-6">
                    <div class="table-container">
                        <div class="table-header">
                            <h5>Recent Tenant Payments</h5>
                        </div>
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Tenant</th>
                                    <th>Plot</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($recent_payments)): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($row['payment_date'])); ?></td>
                                    <td><?php echo $row['tenant_name']; ?></td>
                                    <td><?php echo $row['plot_name']; ?></td>
                                    <td><?php echo formatMoney($row['amount_paid']); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Rent Reminders -->
            <?php if ($reminder_count > 0): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <div class="table-container">
                        <div class="table-header">
                            <h5>Rent Reminders - Tenants Who Haven't Paid This Month</h5>
                        </div>
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tenant ID</th>
                                    <th>Tenant Name</th>
                                    <th>Plot</th>
                                    <th>House</th>
                                    <th>Rent Amount</th>
                                    <th>Days Overdue</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($reminders)): ?>
                                <tr>
                                    <td><?php echo $row['tenant_id']; ?></td>
                                    <td><?php echo $row['tenant_name']; ?></td>
                                    <td><?php echo $row['plot_name']; ?></td>
                                    <td><?php echo $row['house_number']; ?></td>
                                    <td><?php echo formatMoney($row['rent_amount']); ?></td>
                                    <td><span class="badge badge-warning"><?php echo $row['days_overdue']; ?> days</span></td>
                                    <td>
                                        <a href="record_payment.php?tenant_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-success">
                                            <i class="fas fa-money-bill"></i> Record Payment
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>

