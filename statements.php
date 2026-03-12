<?php
require_once 'config.php';
requireLogin();

// Get all plots for the dropdown
$plots = mysqli_query($conn, "SELECT * FROM plots ORDER BY plot_name");

// Get all active tenants for the dropdown
$tenants = mysqli_query($conn, 
    "SELECT t.*, p.plot_name 
     FROM tenants t 
     JOIN plots p ON t.plot_id = p.id 
     WHERE t.status='Active' 
     ORDER BY t.tenant_name"
);

// Handle filter submissions
$selected_plot = isset($_GET['plot_id']) ? $_GET['plot_id'] : '';
$selected_tenant = isset($_GET['tenant_id']) ? $_GET['tenant_id'] : '';

// Get summary statistics
$total_expected = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COALESCE(SUM(rent_amount), 0) as total FROM tenants WHERE status='Active'"
))['total'];

$total_collected = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COALESCE(SUM(amount_paid), 0) as total FROM rent_payments WHERE payment_status='Paid'"
))['total'];

$total_commission = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COALESCE(SUM(commission_amount), 0) as total FROM rent_payments WHERE payment_status='Paid'"
))['total'];

$total_outstanding = $total_expected - mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COALESCE(SUM(amount_paid), 0) as total FROM rent_payments WHERE payment_status='Paid'"
))['total'];

$page_title = 'Statements - Leta Homes Agency';
?>
<?php include 'header.php'; ?>
    
    <div class="layout-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="container-fluid py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-file-invoice"></i> Statements</h2>
            </div>
            
            <!-- Financial Summary Cards -->
            <div class="dashboard-container mb-4">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="card-label">Total Expected Rent</div>
                            <div class="card-value"><?php echo formatMoney($total_expected); ?></div>
                        </div>
                        <div class="card-icon" style="background: #3498db;">
                            <i class="fas fa-money-bill"></i>
                        </div>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="card-label">Total Collected</div>
                            <div class="card-value"><?php echo formatMoney($total_collected); ?></div>
                        </div>
                        <div class="card-icon" style="background: #27ae60;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="card-label">Total Commission</div>
                            <div class="card-value"><?php echo formatMoney($total_commission); ?></div>
                        </div>
                        <div class="card-icon" style="background: #9b59b6;">
                            <i class="fas fa-percent"></i>
                        </div>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="card-label">Outstanding Balance</div>
                            <div class="card-value"><?php echo formatMoney($total_outstanding); ?></div>
                        </div>
                        <div class="card-icon" style="background: #e74c3c;">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filter Section -->
            <div class="table-container mb-4">
                <div class="table-header">
                    <h5>View Statements</h5>
                </div>
                <div class="p-3">
                    <form method="GET" action="statements.php" class="row g-3">
                        <div class="col-md-4">
                            <label for="plot_id" class="form-label">Select Plot</label>
                            <select name="plot_id" id="plot_id" class="form-select">
                                <option value="">-- All Plots --</option>
                                <?php while ($plot = mysqli_fetch_assoc($plots)): ?>
                                    <option value="<?php echo $plot['id']; ?>" <?php echo $selected_plot == $plot['id'] ? 'selected' : ''; ?>>
                                        <?php echo $plot['plot_name']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="tenant_id" class="form-label">Select Tenant</label>
                            <select name="tenant_id" id="tenant_id" class="form-select">
                                <option value="">-- All Tenants --</option>
                                <?php 
                                // Reset tenant pointer
                                mysqli_data_seek($tenants, 0);
                                while ($tenant = mysqli_fetch_assoc($tenants)): ?>
                                    <option value="<?php echo $tenant['id']; ?>" <?php echo $selected_tenant == $tenant['id'] ? 'selected' : ''; ?>>
                                        <?php echo $tenant['tenant_name'] . ' - ' . $tenant['plot_name'] . ' (' . $tenant['house_number'] . ')'; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="statements.php" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="row">
                <div class="col-md-6">
                    <div class="table-container">
                        <div class="table-header">
                            <h5>Tenant Statements</h5>
                        </div>
                        <div class="p-3">
                            <p class="text-muted">View individual tenant statements including payment history and outstanding balance.</p>
                            <a href="tenants.php" class="btn btn-primary">
                                <i class="fas fa-users"></i> View All Tenants
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="table-container">
                        <div class="table-header">
                            <h5>Plot Statements</h5>
                        </div>
                        <div class="p-3">
                            <p class="text-muted">View plot-level financial statements including all tenants and total collections.</p>
                            <a href="plots.php" class="btn btn-primary">
                                <i class="fas fa-building"></i> View All Plots
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activity Summary -->
            <div class="table-container mt-4">
                <div class="table-header">
                    <h5>Monthly Collection Summary</h5>
                </div>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Tenants Paid</th>
                            <th>Total Collected</th>
                            <th>Commission</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $monthly_summary = mysqli_query($conn, 
                            "SELECT DATE_FORMAT(payment_month, '%M %Y') as month,
                                    COUNT(DISTINCT tenant_id) as tenants_paid,
                                    SUM(amount_paid) as total_collected,
                                    SUM(commission_amount) as total_commission
                             FROM rent_payments 
                             WHERE payment_status='Paid'
                             GROUP BY YEAR(payment_month), MONTH(payment_month)
                             ORDER BY payment_month DESC
                             LIMIT 6"
                        );
                        ?>
                        <?php if (mysqli_num_rows($monthly_summary) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($monthly_summary)): ?>
                            <tr>
                                <td><?php echo $row['month']; ?></td>
                                <td><?php echo $row['tenants_paid']; ?></td>
                                <td><?php echo formatMoney($row['total_collected']); ?></td>
                                <td><?php echo formatMoney($row['total_commission']); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">No payment data available</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>

