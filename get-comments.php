<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/database.php';

$response = ['success' => false, 'data' => [], 'message' => ''];

try {
    if (!isset($_GET['upload_id'])) {
        $response['message'] = 'Upload ID is required';
        echo json_encode($response);
        exit();
    }
    
    $upload_id = (int)$_GET['upload_id'];
    
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT 
                c.comment_id,
                c.comment_text,
                c.created_at,
                u.username,
                u.full_name,
                u.profile_picture
              FROM comments c
              JOIN users u ON c.user_id = u.user_id
              WHERE c.upload_id = :upload_id
              ORDER BY c.created_at ASC";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':upload_id', $upload_id);
    $stmt->execute();
    
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response['success'] = true;
    $response['data'] = $comments;
    $response['count'] = count($comments);
    
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
?>