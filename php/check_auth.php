<?php
/**
 * Authentication Checker - Verify user is logged in
 * Used to protect HTML pages via JavaScript AJAX calls
 */

session_start();

header('Content-Type: application/json');

// Check if user is logged in via PHP session
if (isset($_SESSION['user_id'])) {
    echo json_encode([
        'authenticated' => true,
        'user_id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? '',
        'full_name' => $_SESSION['full_name'] ?? ''
    ]);
} else {
    echo json_encode([
        'authenticated' => false,
        'redirect' => '../html/login.html'
    ]);
}
?>

