<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$challenge_id = $_POST['challenge_id'] ?? 0;
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$start_date = $_POST['start_date'] ?? null;
$end_date = $_POST['end_date'] ?? null;
$prize = trim($_POST['prize'] ?? '');
$is_active = $_POST['is_active'] ?? 1;

if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Title required']);
    exit();
}

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

if ($challenge_id > 0) {
    $update = $db->prepare("UPDATE challenges SET title=:title, description=:desc, start_date=:start, end_date=:end, prize=:prize, is_active=:active WHERE challenge_id=:id");
    $result = $update->execute([
        ':title' => $title, ':desc' => $description, ':start' => $start_date,
        ':end' => $end_date, ':prize' => $prize, ':active' => $is_active, ':id' => $challenge_id
    ]);
} else {
    $insert = $db->prepare("INSERT INTO challenges (title, description, start_date, end_date, prize, is_active) VALUES (:title, :desc, :start, :end, :prize, :active)");
    $result = $insert->execute([
        ':title' => $title, ':desc' => $description, ':start' => $start_date,
        ':end' => $end_date, ':prize' => $prize, ':active' => $is_active
    ]);
}

echo json_encode(['success' => true, 'message' => 'Challenge saved']);
?>