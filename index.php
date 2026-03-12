<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: html/dashboard.html');
} else {
    header('Location: html/login.html');
}
exit();
?>
