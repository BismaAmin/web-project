<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$upload_id = $_POST['upload_id'] ?? 0;

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$delete = $db->prepare("DELETE FROM user_uploads WHERE upload_id = :id");
$result = $delete->execute([':id' => $upload_id]);

echo json_encode(['success' => true, 'message' => 'Upload deleted']);
?>