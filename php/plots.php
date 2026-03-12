<?php
session_start();
require_once 'config.php';
requireLogin();

// Handle plot deletion
if (isset($_GET['delete'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete']);
    mysqli_query($conn, "DELETE FROM plots WHERE id = $id");
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit();
}

// Get all plots
$plots = mysqli_query($conn, "SELECT p.*, 
                              (SELECT COUNT(*) FROM tenants WHERE plot_id = p.id AND status='Active') as total_tenants,
                              (SELECT COALESCE(SUM(rent_amount), 0) FROM tenants WHERE plot_id = p.id AND status='Active') as total_rent
                              FROM plots p
                              ORDER BY p.created_at DESC");

$plots_data = [];
while ($row = mysqli_fetch_assoc($plots)) {
    $plots_data[] = [
        'id' => $row['id'],
        'plot_name' => $row['plot_name'],
        'location' => $row['location'],
        'total_tenants' => $row['total_tenants'],
        'total_rent' => formatMoney($row['total_rent']),
        'created_date' => date('d/m/Y', strtotime($row['created_at']))
    ];
}

// Get summary statistics
$total_plots = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM plots"))['count'];
$total_tenants = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM tenants WHERE status='Active'"))['count'];
$total_rent = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(rent_amount), 0) as total FROM tenants WHERE status='Active'"))['total'];

// Calculate occupancy rate (assuming each plot has at least 1 unit)
$total_units = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM plots"))['count'] * 10; // Assuming average of 10 units per plot
$occupancy_rate = $total_units > 0 ? round(($total_tenants / $total_units) * 100) : 0;

header('Content-Type: application/json');
echo json_encode([
    'plots' => $plots_data,
    'summary' => [
        'total_plots' => $total_plots,
        'total_tenants' => $total_tenants,
        'total_rent' => formatMoney($total_rent),
        'occupancy_rate' => $occupancy_rate
    ]
]);
exit();
?>
