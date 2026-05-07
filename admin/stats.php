<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$total_users = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_recipes = $db->query("SELECT COUNT(*) FROM recipes")->fetchColumn();
$total_challenges = $db->query("SELECT COUNT(*) FROM challenges WHERE is_active = 1")->fetchColumn();
$total_uploads = $db->query("SELECT COUNT(*) FROM user_uploads")->fetchColumn();
$total_likes = $db->query("SELECT SUM(total_likes) FROM user_uploads")->fetchColumn();
$total_ingredients = $db->query("SELECT COUNT(*) FROM ingredients")->fetchColumn();

echo json_encode([
    'success' => true,
    'total_users' => (int)$total_users,
    'total_recipes' => (int)$total_recipes,
    'total_challenges' => (int)$total_challenges,
    'total_uploads' => (int)$total_uploads,
    'total_likes' => (int)$total_likes,
    'total_ingredients' => (int)$total_ingredients
]);
?>