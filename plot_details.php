<?php
require_once 'config.php';
requireLogin();

if (!isset($_GET['id'])) {
    header('Location: plots.php');
    exit();
}

$plot_id = mysqli_real_escape_string($conn, $_GET['id']);

// Get plot details
$plot_query = mysqli_query($conn, "SELECT * FROM plots WHERE id = $plot_id");
$plot = mysqli_fetch_assoc($plot_query);

if (!$plot) {
    header('Location: plots.php');
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

$page_title = 'Plot Details - ' . $plot['plot_name'];
?>
<?php include 'header.php'; ?>
    
    <div class="layout-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="container-fluid py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-building"></i> <?php echo $plot['plot_name']; ?></h2>
                <div>
                    <a href="plots.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Plots
                    </a>
                    <a href="plot_statement.php?id=<?php echo $plot_id; ?>" class="btn btn-info">
                        <i class="fas fa-file-invoice"></i> View Statement
                    </a>
                </div>
            </div>
            
            <!-- Plot Information -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="dashboard-card">
                        <h5>Plot Information</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Plot Name:</strong></td>
                                <td><?php echo $plot['plot_name']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Location:</strong></td>
                                <td><?php echo $plot['location'] ?: 'Not specified'; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Created Date:</strong></td>
                                <td><?php echo date('d/m/Y', strtotime($plot['created_at'])); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="dashboard-card">
                        <h5>Financial Summary</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Total Tenants:</strong></td>
                                <td><?php echo $financial_summary['total_tenants']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Total Rent Collected:</strong></td>
                                <td><?php echo formatMoney($financial_summary['total_rent_collected']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Total Unpaid Rent:</strong></td>
                                <td><?php echo formatMoney($unpaid_rent); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Total Commission Earned:</strong></td>
                                <td><?php echo formatMoney($financial_summary['total_commission']); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Tenants List -->
            <div class="table-container">
                <div class="table-header">
                    <h5>Tenants in this Plot</h5>
                    <a href="add_tenant.php?plot_id=<?php echo $plot_id; ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add New Tenant
                    </a>
                </div>
                
                <table class="table table-hover datatable">
                    <thead>
                        <tr>
                            <th>Tenant ID</th>
                            <th>Name</th>
                            <th>House No</th>
                            <th>House Type</th>
                            <th>Rent Amount</th>
                            <th>Commission</th>
                            <th>Move In Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($tenant = mysqli_fetch_assoc($tenants)): ?>
                        <tr>
                            <td><?php echo $tenant['tenant_id']; ?></td>
                            <td><?php echo $tenant['tenant_name']; ?></td>
                            <td><?php echo $tenant['house_number']; ?></td>
                            <td><?php echo $tenant['house_type']; ?></td>
                            <td><?php echo formatMoney($tenant['rent_amount']); ?></td>
                            <td><?php echo $tenant['commission_percentage']; ?>%</td>
                            <td><?php echo date('d/m/Y', strtotime($tenant['move_in_date'])); ?></td>
                            <td>
                                <span class="badge <?php echo $tenant['status'] == 'Active' ? 'badge-active' : 'badge-moved'; ?>">
                                    <?php echo $tenant['status']; ?>
                                </span>
                            </td>
                            <td>
                                <a href="tenant_statement.php?id=<?php echo $tenant['id']; ?>" class="btn btn-sm btn-info btn-action" title="View Statement">
                                    <i class="fas fa-file-invoice"></i>
                                </a>
                                <a href="edit_tenant.php?id=<?php echo $tenant['id']; ?>" class="btn btn-sm btn-warning btn-action" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="record_payment.php?tenant_id=<?php echo $tenant['id']; ?>" class="btn btn-sm btn-success btn-action" title="Record Payment">
                                    <i class="fas fa-money-bill"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>

