<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/database.php';

$response = ['success' => false, 'message' => ''];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Please login to comment';
    echo json_encode($response);
    exit();
}

try {
    if (!isset($_POST['upload_id']) || !isset($_POST['comment_text'])) {
        $response['message'] = 'Missing required fields';
        echo json_encode($response);
        exit();
    }
    
    $upload_id = (int)$_POST['upload_id'];
    $user_id = (int)$_SESSION['user_id'];
    $comment_text = trim($_POST['comment_text']);
    
    if (empty($comment_text)) {
        $response['message'] = 'Comment cannot be empty';
        echo json_encode($response);
        exit();
    }
    
    if (strlen($comment_text) > 500) {
        $response['message'] = 'Comment too long (max 500 characters)';
        echo json_encode($response);
        exit();
    }
    
    $database = new Database();
    $db = $database->getConnection();
    
    // Insert comment
    $query = "INSERT INTO comments (upload_id, user_id, comment_text, created_at) 
              VALUES (:upload_id, :user_id, :comment_text, NOW())";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':upload_id', $upload_id);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':comment_text', $comment_text);
    
    if ($stmt->execute()) {
        $comment_id = $db->lastInsertId();
        
        // Get user info for response
        $user_query = "SELECT username, full_name, profile_picture FROM users WHERE user_id = :user_id";
        $user_stmt = $db->prepare($user_query);
        $user_stmt->bindParam(':user_id', $user_id);
        $user_stmt->execute();
        $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
        
        $response['success'] = true;
        $response['message'] = 'Comment added successfully';
        $response['comment_id'] = $comment_id;
        $response['username'] = $user['username'];
        $response['text'] = $comment_text;
        $response['created_at'] = date('Y-m-d H:i:s');
        $response['profile_picture'] = $user['profile_picture'];
    } else {
        $response['message'] = 'Failed to add comment';
    }
    
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
?>