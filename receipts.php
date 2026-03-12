<?php
require_once 'config.php';
requireLogin();

// Get all receipts with payment and tenant information
$receipts = mysqli_query($conn, 
    "SELECT r.*, rp.payment_date, rp.amount_paid, rp.payment_month,
            t.tenant_name, t.tenant_id, t.house_number, p.plot_name
     FROM receipts r
     JOIN rent_payments rp ON r.payment_id = rp.id
     JOIN tenants t ON r.tenant_id = t.id
     JOIN plots p ON t.plot_id = p.id
     ORDER BY r.created_at DESC"
);

// Get statistics
$total_receipts = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM receipts"));

$total_amount = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COALESCE(SUM(rp.amount_paid), 0) as total 
     FROM receipts r 
     JOIN rent_payments rp ON r.payment_id = rp.id 
     WHERE rp.payment_status='Paid'"
))['total'];

$page_title = 'Receipts Management - Leta Homes Agency';
?>
<?php include 'header.php'; ?>
    
    <div class="layout-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="container-fluid py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-receipt"></i> Receipts Management</h2>
            </div>
            
            <!-- Statistics Cards -->
            <div class="dashboard-container mb-4">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="card-label">Total Receipts</div>
                            <div class="card-value"><?php echo $total_receipts; ?></div>
                        </div>
                        <div class="card-icon" style="background: #3498db;">
                            <i class="fas fa-receipt"></i>
                        </div>
                    </div>
                </div>
                
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="card-label">Total Amount</div>
                            <div class="card-value"><?php echo formatMoney($total_amount); ?></div>
                        </div>
                        <div class="card-icon" style="background: #27ae60;">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Receipts Table -->
            <div class="table-container">
                <div class="table-header">
                    <h5>All Receipts</h5>
                </div>
                
                <table class="table table-hover datatable">
                    <thead>
                        <tr>
                            <th>Receipt No.</th>
                            <th>Date</th>
                            <th>Tenant ID</th>
                            <th>Tenant Name</th>
                            <th>Plot</th>
                            <th>House No</th>
                            <th>Payment Month</th>
                            <th>Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($receipts) > 0): ?>
                            <?php while ($receipt = mysqli_fetch_assoc($receipts)): ?>
                            <tr>
                                <td><strong><?php echo $receipt['receipt_number']; ?></strong></td>
                                <td><?php echo date('d/m/Y', strtotime($receipt['created_at'])); ?></td>
                                <td><?php echo $receipt['tenant_id']; ?></td>
                                <td><?php echo $receipt['tenant_name']; ?></td>
                                <td><?php echo $receipt['plot_name']; ?></td>
                                <td><?php echo $receipt['house_number']; ?></td>
                                <td><?php echo date('M Y', strtotime($receipt['payment_month'])); ?></td>
                                <td><strong><?php echo formatMoney($receipt['amount_paid']); ?></strong></td>
                                <td>
                                    <a href="view_receipt.php?id=<?php echo $receipt['payment_id']; ?>" class="btn btn-sm btn-info btn-action" title="View Receipt">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="print_receipt.php?id=<?php echo $receipt['id']; ?>" class="btn btn-sm btn-secondary btn-action" title="Print Receipt" target="_blank">
                                        <i class="fas fa-print"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">No receipts found</td>
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

