<?php
session_start();
require_once 'config.php';
requireLogin();

if (!isset($_GET['payment_id'])) {
    header('Location: ../html/plots.html');
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
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Receipt not found']);
    exit();
}

$receipt = mysqli_fetch_assoc($receipt_query);

// Format amount in words
$f = new NumberFormatter("en", NumberFormatter::SPELLOUT);
$amount_words = ucwords($f->format($receipt['amount_paid'])) . " Kenyan Shillings Only";

header('Content-Type: application/json');
echo json_encode([
    'receipt' => [
        'receipt_number' => $receipt['receipt_number'],
        'generated_date' => date('d/m/Y', strtotime($receipt['generated_date'])),
        'tenant_id' => $receipt['tenant_id'],
        'tenant_name' => $receipt['tenant_name'],
        'plot_name' => $receipt['plot_name'],
        'house_number' => $receipt['house_number'],
        'payment_date' => date('d/m/Y', strtotime($receipt['payment_date'])),
        'amount_paid' => formatMoney($receipt['amount_paid']),
        'commission' => formatMoney($receipt['commission_amount']),
        'payment_month' => date('F Y', strtotime($receipt['payment_month'])),
        'amount_words' => $amount_words
    ]
]);
exit();
?>
