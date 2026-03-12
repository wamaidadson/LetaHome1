<?php
/**
 * Login Handler - PDO PostgreSQL Version for Supabase
 */
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ../html/dashboard.html');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once 'config_supabase.php';
    $pdo = getDatabaseConnection();
    
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    try {
        // Check user
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Login success
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
            echo json_encode(['success' => false, 'message' => 'Invalid credentials!']);
            exit();
        }
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
        exit();
    }
}
?>

