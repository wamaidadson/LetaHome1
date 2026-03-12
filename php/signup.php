<?php
/**
 * Signup Handler - Create new user account
 * Handles user registration with secure password hashing
 */

session_start();
header('Content-Type: application/json');

// Allow only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

require_once 'config.php';

// Get and sanitize input
$full_name = sanitizeInput($_POST['full_name'] ?? '');
$username = sanitizeInput($_POST['username'] ?? '');
$email = sanitizeInput($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validate required fields
$errors = [];

if (empty($full_name)) {
    $errors[] = 'Full name is required';
} elseif (strlen($full_name) < 2) {
    $errors[] = 'Full name must be at least 2 characters';
}

if (empty($username)) {
    $errors[] = 'Username is required';
} elseif (strlen($username) < 3) {
    $errors[] = 'Username must be at least 3 characters';
} elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    $errors[] = 'Username can only contain letters, numbers, and underscores';
}

if (empty($email)) {
    $errors[] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address';
}

if (empty($password)) {
    $errors[] = 'Password is required';
} elseif (strlen($password) < 6) {
    $errors[] = 'Password must be at least 6 characters';
}

if ($password !== $confirm_password) {
    $errors[] = 'Passwords do not match';
}

// Return validation errors
if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode('. ', $errors)]);
    exit();
}

// Check if username already exists
$check_username = "SELECT id FROM users WHERE username = '$username'";
$result = mysqli_query($conn, $check_username);
if (mysqli_num_rows($result) > 0) {
    echo json_encode(['success' => false, 'message' => 'Username already taken']);
    exit();
}

// Check if email already exists
$check_email = "SELECT id FROM users WHERE email = '$email'";
$result = mysqli_query($conn, $check_email);
if (mysqli_num_rows($result) > 0) {
    echo json_encode(['success' => false, 'message' => 'Email already registered']);
    exit();
}

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Generate unique user ID
$user_id = 'USR-' . date('Y') . '-' . str_pad(mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users")) + 1, 4, '0', STR_PAD_LEFT);

// Insert new user
$insert_query = "INSERT INTO users (user_id, full_name, username, email, password, created_at) 
                 VALUES ('$user_id', '$full_name', '$username', '$email', '$hashed_password', NOW())";

if (mysqli_query($conn, $insert_query)) {
    // Get the inserted user
    $new_user_id = mysqli_insert_id($conn);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Account created successfully! You can now login.',
        'redirect' => 'login.html'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error creating account. Please try again.']);
}

mysqli_close($conn);
?>

