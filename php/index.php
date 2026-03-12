<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: ../html/dashboard.php');
} else {
    header('Location: ../html/login.php');
}
exit();
?>
