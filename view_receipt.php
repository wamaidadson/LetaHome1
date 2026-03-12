<?php
require_once 'config.php';
requireLogin();

if (!isset($_GET['payment_id'])) {
    header('Location: payments.php');
    exit();
}

$payment_id = mysqli_real_escape_string($conn, $_GET['payment_id']);

// Get receipt details
$receipt_query = mysqli_query($conn,
    "SELECT r.*, rp.*, t.tenant_id, t.tenant_name, t.house_number, t.commission_percentage,
            p.plot_name
     FROM receipts r
     JOIN rent_payments rp ON r.payment_id = rp.id
     JOIN tenants t ON rp.tenant_id = t.id
     JOIN plots p ON t.plot_id = p.id
     WHERE rp.id = $payment_id"
);

if (mysqli_num_rows($receipt_query) == 0) {
    header('Location: payments.php');
    exit();
}

$receipt = mysqli_fetch_assoc($receipt_query);

$page_title = 'Payment Receipt - Leta Homes Agency';
?>
<?php include 'header.php'; ?>
    
    <div class="layout-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="container-fluid py-4">
            <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                <h2><i class="fas fa-receipt"></i> Payment Receipt</h2>
                <div>
                    <button onclick="window.print()" class="btn btn-primary me-2">
                        <i class="fas fa-print"></i> Print Receipt
                    </button>
                    <a href="payments.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Payments
                    </a>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <div class="receipt-container" id="receipt">
                        <div class="receipt-header">
                            <h2>LETA HOMES AGENCY</h2>
                            <p>Property Management Solutions</p>
                            <h3>OFFICIAL RECEIPT</h3>
                        </div>
                        
                        <div class="receipt-details">
                            <table class="receipt-table">
                                <tr>
                                    <td>Receipt No:</td>
                                    <td><strong><?php echo $receipt['receipt_number']; ?></strong></td>
                                </tr>
                                <tr>
                                    <td>Date:</td>
                                    <td><?php echo date('d/m/Y', strtotime($receipt['generated_date'])); ?></td>
                                </tr>
                                <tr>
                                    <td>Tenant ID:</td>
                                    <td><?php echo $receipt['tenant_id']; ?></td>
                                </tr>
                                <tr>
                                    <td>Tenant Name:</td>
                                    <td><?php echo $receipt['tenant_name']; ?></td>
                                </tr>
                                <tr>
                                    <td>Plot:</td>
                                    <td><?php echo $receipt['plot_name']; ?></td>
                                </tr>
                                <tr>
                                    <td>House Number:</td>
                                    <td><?php echo $receipt['house_number']; ?></td>
                                </tr>
                                <tr>
                                    <td>Payment Date:</td>
                                    <td><?php echo date('d/m/Y', strtotime($receipt['payment_date'])); ?></td>
                                </tr>
                                <tr>
                                    <td>Amount Paid:</td>
                                    <td><strong><?php echo formatMoney($receipt['amount_paid']); ?></strong></td>
                                </tr>
                                <tr>
                                    <td>Commission (<?php echo $receipt['commission_percentage']; ?>%):</td>
                                    <td><?php echo formatMoney($receipt['commission_amount']); ?></td>
                                </tr>
                                <tr>
                                    <td>Payment For:</td>
                                    <td><?php echo date('F Y', strtotime($receipt['payment_month'])); ?></td>
                                </tr>
                            </table>
                            
                            <div style="text-align: center; margin: 30px 0;">
                                <p><strong>Amount in Words:</strong></p>
                                <p><?php 
                                    $f = new NumberFormatter("en", NumberFormatter::SPELLOUT);
                                    echo ucwords($f->format($receipt['amount_paid'])) . " Kenyan Shillings Only";
                                ?></p>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; margin-top: 50px;">
                                <div>
                                    <p>_________________________</p>
                                    <p>Received By</p>
                                </div>
                                <div>
                                    <p>_________________________</p>
                                    <p>Tenant's Signature</p>
                                </div>
                            </div>
                            
                            <div class="receipt-footer">
                                <p>This is a computer generated receipt. No signature required.</p>
                                <p>Thank you for your payment!</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <style>
    @media print {
        body {
            background: white;
        }
        .main-content {
            margin: 0;
            padding: 20px;
        }
        .receipt-container {
            box-shadow: none;
            padding: 0;
        }
    }
    </style>
    
    <?php include 'footer.php'; ?>
</body>
</html>

