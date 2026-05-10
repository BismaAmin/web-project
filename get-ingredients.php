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

$ingredients = $db->query("SELECT * FROM ingredients ORDER BY ingredient_id DESC")->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['success' => true, 'data' => $ingredients]);
?>