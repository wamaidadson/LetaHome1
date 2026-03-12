<?php
require_once 'config.php';
requireLogin();

if (!isset($_GET['id'])) {
    header('Location: tenants.php');
    exit();
}

$tenant_id = mysqli_real_escape_string($conn, $_GET['id']);

// Get tenant details
$tenant_query = mysqli_query($conn,
    "SELECT t.*, p.plot_name 
     FROM tenants t
     JOIN plots p ON t.plot_id = p.id
     WHERE t.id = $tenant_id"
);
$tenant = mysqli_fetch_assoc($tenant_query);

if (!$tenant) {
    header('Location: tenants.php');
    exit();
}

// Get payment history
$payments = mysqli_query($conn,
    "SELECT * FROM rent_payments 
     WHERE tenant_id = $tenant_id 
     ORDER BY payment_date DESC"
);

// Calculate totals
$total_paid = 0;
$months_unpaid = 0;
$last_payment_date = null;

$payment_months = [];
while ($payment = mysqli_fetch_assoc($payments)) {
    if ($payment['payment_status'] == 'Paid') {
        $total_paid += $payment['amount_paid'];
        $payment_months[] = date('Y-m', strtotime($payment['payment_month']));
    }
}
mysqli_data_seek($payments, 0);

// Calculate months since move in
$move_in = new DateTime($tenant['move_in_date']);
$now = new DateTime();
$interval = $move_in->diff($now);
$total_months = ($interval->y * 12) + $interval->m + 1;

// Calculate expected rent
$expected_rent = $total_months * $tenant['rent_amount'];
$outstanding_balance = $expected_rent - $total_paid;

// Calculate months unpaid
$current_month = date('Y-m');
for ($i = 0; $i < $total_months; $i++) {
    $month = date('Y-m', strtotime("-$i months"));
    if (!in_array($month, $payment_months)) {
        $months_unpaid++;
    }
}

$page_title = 'Tenant Statement - ' . $tenant['tenant_name'];
?>
<?php include 'header.php'; ?>
    
    <div class="layout-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="container-fluid py-4">
            <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                <h2><i class="fas fa-file-invoice"></i> Tenant Statement</h2>
                <div>
                    <button onclick="window.print()" class="btn btn-primary me-2">
                        <i class="fas fa-print"></i> Print Statement
                    </button>
                    <a href="plot_details.php?id=<?php echo $tenant['plot_id']; ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12">
                    <div class="table-container">
                        <h4 class="mb-4">Tenant Statement - <?php echo $tenant['tenant_name']; ?></h4>
                        
                        <!-- Tenant Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Tenant ID:</strong></td>
                                        <td><?php echo $tenant['tenant_id']; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tenant Name:</strong></td>
                                        <td><?php echo $tenant['tenant_name']; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Phone Number:</strong></td>
                                        <td><?php echo $tenant['phone_number'] ?: 'N/A'; ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Plot Name:</strong></td>
                                        <td><?php echo $tenant['plot_name']; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>House Number:</strong></td>
                                        <td><?php echo $tenant['house_number']; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>House Type:</strong></td>
                                        <td><?php echo $tenant['house_type']; ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Summary Cards -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="dashboard-card">
                                    <div class="card-label">Rent Amount</div>
                                    <div class="card-value"><?php echo formatMoney($tenant['rent_amount']); ?></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="dashboard-card">
                                    <div class="card-label">Total Paid</div>
                                    <div class="card-value"><?php echo formatMoney($total_paid); ?></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="dashboard-card">
                                    <div class="card-label">Outstanding Balance</div>
                                    <div class="card-value"><?php echo formatMoney($outstanding_balance); ?></div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="dashboard-card">
                                    <div class="card-label">Months Unpaid</div>
                                    <div class="card-value"><?php echo $months_unpaid; ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Payment History -->
                        <h5 class="mb-3">Payment History</h5>
                        <table class="table table-hover datatable">
                            <thead>
                                <tr>
                                    <th>Payment Date</th>
                                    <th>Amount Paid</th>
                                    <th>Commission</th>
                                    <th>Payment Month</th>
                                    <th>Status</th>
                                    <th>Receipt</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($payments) > 0): ?>
                                    <?php while ($payment = mysqli_fetch_assoc($payments)): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($payment['payment_date'])); ?></td>
                                        <td><?php echo formatMoney($payment['amount_paid']); ?></td>
                                        <td><?php echo formatMoney($payment['commission_amount']); ?></td>
                                        <td><?php echo date('F Y', strtotime($payment['payment_month'])); ?></td>
                                        <td>
                                            <span class="badge <?php echo $payment['payment_status'] == 'Paid' ? 'badge-paid' : 'badge-unpaid'; ?>">
                                                <?php echo $payment['payment_status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $receipt_query = mysqli_query($conn, "SELECT receipt_number FROM receipts WHERE payment_id = " . $payment['id']);
                                            if (mysqli_num_rows($receipt_query) > 0) {
                                                $receipt = mysqli_fetch_assoc($receipt_query);
                                                echo $receipt['receipt_number'];
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No payment records found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        
                        <!-- Arrears Summary -->
                        <?php if ($months_unpaid > 0): ?>
                        <div class="alert alert-warning mt-4">
                            <h5><i class="fas fa-exclamation-triangle"></i> Arrears Summary</h5>
                            <p>This tenant has <strong><?php echo $months_unpaid; ?> month(s)</strong> of unpaid rent.</p>
                            <p>Outstanding balance: <strong><?php echo formatMoney($outstanding_balance); ?></strong></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <style>
    @media print {
        .no-print {
            display: none !important;
        }
        .main-content {
            margin: 0;
            padding: 20px;
        }
        .table-container {
            box-shadow: none;
            padding: 0;
        }
    }
    </style>
    
    <?php include 'footer.php'; ?>
</body>
</html>

