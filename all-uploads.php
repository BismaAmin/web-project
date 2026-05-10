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

$uploads = $db->query("SELECT u.*, us.username FROM user_uploads u JOIN users us ON u.user_id = us.user_id ORDER BY u.upload_id DESC")->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['success' => true, 'data' => $uploads]);
?>