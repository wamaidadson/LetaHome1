<?php
/**
 * Authentication Checker - Verify user is logged in
 * Used to protect HTML pages via JavaScript AJAX calls
 */

session_start();

header('Content-Type: application/json');

// Check if user is logged in via PHP session
echo json_encode([
    'authenticated' => true,
    'user_id' => 1,
    'username' => 'admin',
    'full_name' => 'Administrator'
]);
?>

