 <?php
require_once 'config.php';
requireLogin();

// Get all payments with tenant and plot information
$payments = mysqli_query($conn, 
    "SELECT rp.*, t.tenant_name, t.tenant_id, t.house_number, p.plot_name
     FROM rent_payments rp
     JOIN tenants t ON rp.tenant_id = t.id
     JOIN plots p ON t.plot_id = p.id
     ORDER BY rp.payment_date DESC"
);

// Get statistics
$total_collected = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COALESCE(SUM(amount_paid), 0) as total FROM rent_payments WHERE payment_status='Paid'"
))['total'];

$total_commission = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COALESCE(SUM(commission_amount), 0) as total FROM rent_payments WHERE payment_status='Paid'"
))['total'];

$pending_payments = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as count FROM rent_payments WHERE payment_status='Pending'"
))['count'];

$total_payments = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM rent_payments"));

$page_title = 'Payments Management - Leta Homes Agency';
?>
<?php include 'header.php'; ?>
    
    <div class="layout-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="container-fluid py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-money-bill"></i> Payments Management</h2>
            </div>
            
            <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                    if ($_GET['msg'] == 'recorded') echo 'Payment recorded successfully!';
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Statistics Cards -->
            <div class="dashboard-container mb-4">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="card-label">Total Payments</div>
                            <div class="card-value"><?php echo $total_payments; ?></div>
                        </div>
                        <div class="card-icon" style="background: #3498db;">
                            <i class="fas fa-receipt"></i>
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
                            <i class="fas fa-money-bill-wave"></i>
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
                            <div class="card-label">Pending Payments</div>
                            <div class="card-value"><?php echo $pending_payments; ?></div>
                        </div>
                        <div class="card-icon" style="background: #f39c12;">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Payments Table -->
            <div class="table-container">
                <div class="table-header">
                    <h5>All Rent Payments</h5>
                </div>
                
                <table class="table table-hover datatable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Tenant ID</th>
                            <th>Tenant Name</th>
                            <th>Plot</th>
                            <th>House No</th>
                            <th>Payment Month</th>
                            <th>Amount Paid</th>
                            <th>Commission</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($payment = mysqli_fetch_assoc($payments)): ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($payment['payment_date'])); ?></td>
                            <td><?php echo $payment['tenant_id']; ?></td>
                            <td><?php echo $payment['tenant_name']; ?></td>
                            <td><?php echo $payment['plot_name']; ?></td>
                            <td><?php echo $payment['house_number']; ?></td>
                            <td><?php echo date('M Y', strtotime($payment['payment_month'])); ?></td>
                            <td><strong><?php echo formatMoney($payment['amount_paid']); ?></strong></td>
                            <td><?php echo formatMoney($payment['commission_amount']); ?></td>
                            <td>
                                <?php if ($payment['payment_status'] == 'Paid'): ?>
                                    <span class="badge badge-active">Paid</span>
                                <?php elseif ($payment['payment_status'] == 'Pending'): ?>
                                    <span class="badge badge-warning">Pending</span>
                                <?php elseif ($payment['payment_status'] == 'Partial'): ?>
                                    <span class="badge badge-warning">Partial</span>
                                <?php else: ?>
                                    <span class="badge badge-moved">Cancelled</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="view_receipt.php?id=<?php echo $payment['id']; ?>" class="btn btn-sm btn-info btn-action" title="View Receipt">
                                    <i class="fas fa-receipt"></i>
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

