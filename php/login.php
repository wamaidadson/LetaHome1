<?php
/**
 * Login Handler - User authentication
 * Supports both hashed passwords (new) and legacy plain text (for backward compatibility)
 */

session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ../html/dashboard.html');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once 'config.php';
    
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    
    // First check if users table exists and has any users
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
    if (mysqli_num_rows($table_check) == 0) {
        // Create users table
        $create_table = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id VARCHAR(20) UNIQUE,
            full_name VARCHAR(100) NOT NULL,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        mysqli_query($conn, $create_table);
    }
    
    // Check if any users exist, if not create default admin
    $user_count_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
    $user_count = mysqli_fetch_assoc($user_count_result)['count'];
    if ($user_count == 0) {
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        $insert_admin = "INSERT INTO users (user_id, full_name, username, email, password, created_at) 
                         VALUES ('USR-2026-0001', 'Administrator', 'admin', 'admin@leta.com', '$admin_password', NOW())";
        mysqli_query($conn, $insert_admin);
    }
    
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Check if password is hashed (starts with $2y$ or $2a$) or legacy plain text
        if (password_get_info($user['password'])['algo'] != 0) {
            // Use password_verify for hashed passwords
            if (password_verify($password, $user['password'])) {
                // Update password hash if needed (rehash)
                if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
                    $new_hash = password_hash($password, PASSWORD_DEFAULT);
                    mysqli_query($conn, "UPDATE users SET password = '$new_hash' WHERE id = " . $user['id']);
                }
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_id_str'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'user_id' => $user['id'],
                    'username' => $user['username'],
                    'full_name' => $user['full_name']
                ]);
                exit();
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid password!']);
                exit();
            }
        } else {
            // Legacy plain text password check (for backward compatibility)
            if ($password == $user['password']) {
                // Upgrade to hashed password
                $new_hash = password_hash($password, PASSWORD_DEFAULT);
                mysqli_query($conn, "UPDATE users SET password = '$new_hash' WHERE id = " . $user['id']);
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_id_str'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'user_id' => $user['id'],
                    'username' => $user['username'],
                    'full_name' => $user['full_name']
                ]);
                exit();
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid password!']);
                exit();
            }
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid username!']);
        exit();
    }
}
?>

