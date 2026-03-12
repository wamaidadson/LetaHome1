<?php
session_start();
require_once 'config.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $plot_name = mysqli_real_escape_string($conn, $_POST['plot_name']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    
    $query = "INSERT INTO plots (plot_name, location) VALUES ('$plot_name', '$location')";
    
    if (mysqli_query($conn, $query)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Plot added successfully']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error: ' . mysqli_error($conn)]);
    }
    exit();
}
?>
