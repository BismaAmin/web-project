<?php
// Include this at the top of all protected PHP files
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.html');
    exit();
}
?>