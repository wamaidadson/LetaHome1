<?php
session_start();
require_once 'config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $plot_name = trim($_POST['plot_name'] ?? '');
    $location = trim($_POST['location'] ?? '');
    
    if (empty($plot_name)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Plot name is required']);
        exit();
    }
    
    $stmt = $conn->prepare("INSERT INTO plots (plot_name, location) VALUES (?, ?)");
    $stmt->bind_param("ss", $plot_name, $location);
    
    if ($stmt->execute()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Plot added successfully', 'id' => $conn->insert_id]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
    }
    $stmt->close();
    exit();
}
?>
