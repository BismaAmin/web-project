<?php
session_start();
header('Content-Type: application/json');

$response = ['logged_in' => false, 'role' => '', 'username' => ''];

if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    $response['logged_in'] = true;
    $response['role'] = $_SESSION['role'] ?? 'user';
    $response['username'] = $_SESSION['username'] ?? '';
    $response['user_id'] = $_SESSION['user_id'];
    $response['email'] = $_SESSION['email'] ?? '';
    $response['full_name'] = $_SESSION['full_name'] ?? '';
}

echo json_encode($response);
?>
