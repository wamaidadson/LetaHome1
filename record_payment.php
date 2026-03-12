<?php
require_once 'config.php';
requireLogin();

if (!isset($_GET['tenant_id'])) {
    header('Location: tenants.php');
    exit();
}

$tenant_id = mysqli_real_escape_string($conn, $_GET['tenant_id']);

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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_date = mysqli_real_escape_string($conn, $_POST['payment_date']);
    $amount_paid = mysqli_real_escape_string($conn, $_POST['amount_paid']);
    $commission_percentage = $tenant['commission_percentage'];
    $commission_amount = ($amount_paid * $commission_percentage) / 100;
    $payment_month = date('Y-m-01', strtotime($payment_date));
    
    // Insert payment
    $query = "INSERT INTO rent_payments (tenant_id, payment_date, amount_paid, commission_percentage, 
                                         commission_amount, payment_status, payment_month) 
              VALUES ('$tenant_id', '$payment_date', '$amount_paid', '$commission_percentage', 
                      '$commission_amount', 'Paid', '$payment_month')";
    
    if (mysqli_query($conn, $query)) {
        $payment_id = mysqli_insert_id($conn);
        
        // Generate receipt
        $receipt_number = generateReceiptNumber($conn);
        mysqli_query($conn, "INSERT INTO receipts (receipt_number, payment_id, tenant_id) 
                            VALUES ('$receipt_number', '$payment_id', '$tenant_id')");
        
        header('Location: view_receipt.php?payment_id=' . $payment_id);
        exit();
    } else {
        $error = "Error recording payment: " . mysqli_error($conn);
    }
}

$page_title = 'Record Payment - Leta Homes Agency';
?>
<?php include 'header.php'; ?>
    
    <div class="layout-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="container-fluid py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-money-bill"></i> Record Rent Payment</h2>
                <a href="plot_details.php?id=<?php echo $tenant['plot_id']; ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
            
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <div class="table-container">
                        <h5 class="mb-4">Tenant Information</h5>
                        <table class="table table-borderless mb-4">
                            <tr>
                                <td><strong>Tenant ID:</strong></td>
                                <td><?php echo $tenant['tenant_id']; ?></td>
                                <td><strong>Tenant Name:</strong></td>
                                <td><?php echo $tenant['tenant_name']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Plot:</strong></td>
                                <td><?php echo $tenant['plot_name']; ?></td>
                                <td><strong>House Number:</strong></td>
                                <td><?php echo $tenant['house_number']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Rent Amount:</strong></td>
                                <td><?php echo formatMoney($tenant['rent_amount']); ?></td>
                                <td><strong>Commission:</strong></td>
                                <td><?php echo $tenant['commission_percentage']; ?>%</td>
                            </tr>
                        </table>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="payment_date" class="form-label">Payment Date *</label>
                                <input type="date" class="form-control" id="payment_date" name="payment_date" 
                                       value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="amount_paid" class="form-label">Amount Paid (KSh) *</label>
                                <input type="number" class="form-control" id="amount_paid" name="amount_paid" 
                                       value="<?php echo $tenant['rent_amount']; ?>" required>
                                <div class="form-text">Commission (<?php echo $tenant['commission_percentage']; ?>%) will be calculated automatically: 
                                    KSh <?php echo number_format(($tenant['rent_amount'] * $tenant['commission_percentage']) / 100, 2); ?></div>
                            </div>
                            
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check"></i> Record Payment & Generate Receipt
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    document.getElementById('amount_paid').addEventListener('input', function() {
        var amount = this.value;
        var commission = <?php echo $tenant['commission_percentage']; ?>;
        var commissionAmount = (amount * commission) / 100;
        document.querySelector('.form-text').innerHTML = 
            'Commission (' + commission + '%) will be calculated automatically: KSh ' + 
            commissionAmount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    });
    </script>
    
    <?php include 'footer.php'; ?>
</body>
</html>

