<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$ingredient_id = $_POST['ingredient_id'] ?? 0;

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$delete = $db->prepare("DELETE FROM ingredients WHERE ingredient_id = :id");
$result = $delete->execute([':id' => $ingredient_id]);

echo json_encode(['success' => true, 'message' => 'Ingredient deleted']);
?>