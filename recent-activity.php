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

$activities = $db->query("
    SELECT 'User registered' as activity, username, created_at 
    FROM users 
    UNION 
    SELECT CONCAT('Uploaded: ', dish_name) as activity, us.username, u.created_at 
    FROM user_uploads u 
    JOIN users us ON u.user_id = us.user_id 
    ORDER BY created_at DESC LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['success' => true, 'data' => $activities]);
?>