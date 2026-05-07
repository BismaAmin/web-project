<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_POST['user_id'] ?? 0;
$full_name = trim($_POST['full_name'] ?? '');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? 'user';
$is_active = $_POST['is_active'] ?? 1;
$bio = trim($_POST['bio'] ?? '');

if (empty($full_name) || empty($username) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Required fields missing']);
    exit();
}

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

if ($user_id > 0) {
    // Update existing user
    if (!empty($password)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $update = $db->prepare("UPDATE users SET full_name=:full_name, username=:username, email=:email, password_hash=:hash, role=:role, is_active=:is_active, bio=:bio WHERE user_id=:user_id");
        $result = $update->execute([
            ':full_name' => $full_name, ':username' => $username, ':email' => $email,
            ':hash' => $hash, ':role' => $role, ':is_active' => $is_active,
            ':bio' => $bio, ':user_id' => $user_id
        ]);
    } else {
        $update = $db->prepare("UPDATE users SET full_name=:full_name, username=:username, email=:email, role=:role, is_active=:is_active, bio=:bio WHERE user_id=:user_id");
        $result = $update->execute([
            ':full_name' => $full_name, ':username' => $username, ':email' => $email,
            ':role' => $role, ':is_active' => $is_active, ':bio' => $bio, ':user_id' => $user_id
        ]);
    }
} else {
    // Create new user
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $insert = $db->prepare("INSERT INTO users (full_name, username, email, password_hash, role, is_active, bio) VALUES (:full_name, :username, :email, :hash, :role, :is_active, :bio)");
    $result = $insert->execute([
        ':full_name' => $full_name, ':username' => $username, ':email' => $email,
        ':hash' => $hash, ':role' => $role, ':is_active' => $is_active, ':bio' => $bio
    ]);
}

echo json_encode(['success' => true, 'message' => 'User saved']);
?>