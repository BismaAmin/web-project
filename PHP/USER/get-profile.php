<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$query = "SELECT user_id, full_name, username, email, bio, profile_picture,
          total_uploads, total_likes_received, total_challenges_joined, total_challenges_won,
          created_at
          FROM users WHERE user_id = :user_id";

$stmt = $db->prepare($query);
$stmt->execute([':user_id' => $_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode(['success' => true, 'data' => $user]);
?>
