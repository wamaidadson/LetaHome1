<?php
require_once 'config.php';
requireLogin();

// Get all tenants with arrears
$arrears_query = "SELECT t.*, p.plot_name,
                  COUNT(CASE WHEN rp.payment_status = 'Not Paid' OR rp.id IS NULL THEN 1 END) as months_unpaid,
                  (t.rent_amount * COUNT(CASE WHEN rp.payment_status = 'Not Paid' OR rp.id IS NULL THEN 1 END)) as outstanding_balance
                  FROM tenants t
                  JOIN plots p ON t.plot_id = p.id
                  LEFT JOIN rent_payments rp ON t.id = rp.tenant_id 
                  WHERE t.status = 'Active'
                  GROUP BY t.id
                  HAVING months_unpaid > 0
                  ORDER BY outstanding_balance DESC";

$tenants_in_arrears = mysqli_query($conn, $arrears_query);

// Calculate total arrears
$total_arrears = 0;
$temp_result = mysqli_query($conn, $arrears_query);
while ($row = mysqli_fetch_assoc($temp_result)) {
    $total_arrears += $row['outstanding_balance'];
}
mysqli_data_seek($tenants_in_arrears, 0);

$page_title = 'Rent Arrears - Leta Homes Agency';
?>
<?php include 'header.php'; ?>
    
    <div class="layout-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="container-fluid py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-exclamation-triangle"></i> Rent Arrears</h2>
            </div>
            
            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="dashboard-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="card-label">Tenants in Arrears</div>
                                <div class="card-value"><?php echo mysqli_num_rows($tenants_in_arrears); ?></div>
                            </div>
                            <div class="card-icon" style="background: #e74c3c;">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="dashboard-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="card-label">Total Outstanding Arrears</div>
                                <div class="card-value"><?php echo formatMoney($total_arrears); ?></div>
                            </div>
                            <div class="card-icon" style="background: #c0392b;">
                                <i class="fas fa-money-bill"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Arrears Table -->
            <div class="table-container">
                <div class="table-header">
                    <h5>Tenants with Outstanding Arrears</h5>
                    <button onclick="window.print()" class="btn btn-primary btn-sm">
                        <i class="fas fa-print"></i> Print Report
                    </button>
                </div>
                
                <table class="table table-hover datatable">
                    <thead>
                        <tr>
                            <th>Tenant ID</th>
                            <th>Tenant Name</th>
                            <th>Plot</th>
                            <th>House No</th>
                            <th>Monthly Rent</th>
                            <th>Months Unpaid</th>
                            <th>Outstanding Balance</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($tenants_in_arrears) > 0): ?>
                            <?php while ($tenant = mysqli_fetch_assoc($tenants_in_arrears)): ?>
                            <tr>
                                <td><?php echo $tenant['tenant_id']; ?></td>
                                <td><?php echo $tenant['tenant_name']; ?></td>
                                <td><?php echo $tenant['plot_name']; ?></td>
                                <td><?php echo $tenant['house_number']; ?></td>
                                <td><?php echo formatMoney($tenant['rent_amount']); ?></td>
                                <td><span class="badge badge-danger"><?php echo $tenant['months_unpaid']; ?> month(s)</span></td>
                                <td><strong><?php echo formatMoney($tenant['outstanding_balance']); ?></strong></td>
                                <td>
                                    <a href="record_payment.php?tenant_id=<?php echo $tenant['id']; ?>" class="btn btn-sm btn-success">
                                        <i class="fas fa-money-bill"></i> Record Payment
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">No tenants with arrears found</td>
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

