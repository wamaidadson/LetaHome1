<?php
require_once 'config.php';
requireLogin();

// Get all tenants with plot information
$tenants = mysqli_query($conn, 
    "SELECT t.*, p.plot_name,
     (SELECT COALESCE(SUM(amount_paid), 0) FROM rent_payments WHERE tenant_id = t.id AND payment_status='Paid') as total_paid,
     (SELECT COUNT(*) FROM rent_payments WHERE tenant_id = t.id AND payment_status='Paid') as payments_count
     FROM tenants t
     JOIN plots p ON t.plot_id = p.id
     ORDER BY t.tenant_name ASC"
);

// Get statistics
$total_tenants = mysqli_num_rows($tenants);
$active_tenants = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tenants WHERE status='Active'"));
$inactive_tenants = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tenants WHERE status!='Active'"));

// Reset the result pointer
mysqli_data_seek($tenants, 0);

$page_title = 'Tenants Management - Leta Homes Agency';
?>
<?php include 'header.php'; ?>
    
    <div class="layout-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="container-fluid py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-users"></i> Tenants Management</h2>
                <a href="add_tenant.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Tenant
                </a>
            </div>
            
            <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                    if ($_GET['msg'] == 'added') echo 'Tenant added successfully!';
                    if ($_GET['msg'] == 'updated') echo 'Tenant updated successfully!';
                    if ($_GET['msg'] == 'deleted') echo 'Tenant deleted successfully!';
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Statistics Cards -->
            <div class="dashboard-container mb-4">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="card-label">Total Tenants</div>
                            <div class="card-value"><?php echo $total_tenants; ?></div>
                        </div>
                        <div class="card-icon" style="background: #3498db;">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="card-label">Active Tenants</div>
                            <div class="card-value"><?php echo $active_tenants; ?></div>
                        </div>
                        <div class="card-icon" style="background: #27ae60;">
                            <i class="fas fa-user-check"></i>
                        </div>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="card-label">Inactive/Moved Out</div>
                            <div class="card-value"><?php echo $inactive_tenants; ?></div>
                        </div>
                        <div class="card-icon" style="background: #95a5a6;">
                            <i class="fas fa-user-times"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tenants Table -->
            <div class="table-container">
                <div class="table-header">
                    <h5>All Tenants</h5>
                </div>
                
                <table class="table table-hover datatable">
                    <thead>
                        <tr>
                            <th>Tenant ID</th>
                            <th>Name</th>
                            <th>Plot</th>
                            <th>House No</th>
                            <th>House Type</th>
                            <th>Rent Amount</th>
                            <th>Total Paid</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($tenant = mysqli_fetch_assoc($tenants)): ?>
                        <tr>
                            <td><?php echo $tenant['tenant_id']; ?></td>
                            <td>
                                <strong><?php echo $tenant['tenant_name']; ?></strong>
                                <br><small class="text-muted"><?php echo $tenant['phone_number']; ?></small>
                            </td>
                            <td><?php echo $tenant['plot_name']; ?></td>
                            <td><?php echo $tenant['house_number']; ?></td>
                            <td><?php echo $tenant['house_type']; ?></td>
                            <td><?php echo formatMoney($tenant['rent_amount']); ?></td>
                            <td><?php echo formatMoney($tenant['total_paid']); ?></td>
                            <td>
                                <span class="badge <?php echo $tenant['status'] == 'Active' ? 'badge-active' : 'badge-moved'; ?>">
                                    <?php echo $tenant['status']; ?>
                                </span>
                            </td>
                            <td>
                                <a href="tenant_statement.php?id=<?php echo $tenant['id']; ?>" class="btn btn-sm btn-info btn-action" title="View Statement">
                                    <i class="fas fa-file-invoice"></i>
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

