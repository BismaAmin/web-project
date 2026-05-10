<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$challenge_id = $_POST['challenge_id'] ?? 0;

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$delete = $db->prepare("DELETE FROM challenges WHERE challenge_id = :id");
$result = $delete->execute([':id' => $challenge_id]);

echo json_encode(['success' => true, 'message' => 'Challenge deleted']);
?>