<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$recipe_id = $_POST['recipe_id'] ?? 0;

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$delete = $db->prepare("DELETE FROM recipes WHERE recipe_id = :id");
$result = $delete->execute([':id' => $recipe_id]);

echo json_encode(['success' => true, 'message' => 'Recipe deleted']);
?>