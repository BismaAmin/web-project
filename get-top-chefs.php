<?php
header('Content-Type: application/json');

require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    $stmt = $db->query("
        SELECT user_id, username, full_name, total_uploads, total_likes_received, profile_picture
        FROM users
        WHERE is_active = 1
        ORDER BY total_uploads DESC, total_likes_received DESC
        LIMIT 4
        //top 4 chesfs
    ");
    $chefs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $chefs]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'data' => []]);
}
?>
