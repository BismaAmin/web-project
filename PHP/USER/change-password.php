<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';

if (empty($current_password) || empty($new_password)) {
    echo json_encode(['success' => false, 'message' => 'All fields required']);
    exit();
}

if (strlen($new_password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
    exit();
}

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get current password hash
$query = "SELECT password_hash FROM users WHERE user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->execute([':user_id' => $_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || !password_verify($current_password, $user['password_hash'])) {
    echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
    exit();
}

// Update with new password
$new_hash = password_hash($new_password, PASSWORD_DEFAULT);
$update = $db->prepare("UPDATE users SET password_hash = :hash WHERE user_id = :user_id");
$result = $update->execute([':hash' => $new_hash, ':user_id' => $_SESSION['user_id']]);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Password changed successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Password change failed']);
}
?>