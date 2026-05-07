<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_POST['user_id'] ?? 0;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit();
}

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$delete = $db->prepare("DELETE FROM users WHERE user_id = :user_id");
$result = $delete->execute([':user_id' => $user_id]);

echo json_encode(['success' => true, 'message' => 'User deleted']);
?>