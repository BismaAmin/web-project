<?php
session_start();
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

$response = ['success' => false, 'message' => '', 'likes' => 0];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Please login to like';
    echo json_encode($response);
    exit();
}

$user_id = $_SESSION['user_id'];
$upload_id = isset($_POST['upload_id']) ? (int)$_POST['upload_id'] : 0;
$action = isset($_POST['action']) ? $_POST['action'] : 'like';

if ($upload_id <= 0) {
    $response['message'] = 'Invalid upload ID';
    echo json_encode($response);
    exit();
}

require_once __DIR__ . '/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        $response['message'] = 'Database connection failed';
        echo json_encode($response);
        exit();
    }
    
    if ($action === 'like') {
        // Check if already liked
        $checkQuery = "SELECT like_id FROM likes WHERE user_id = :user_id AND upload_id = :upload_id";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->execute([':user_id' => $user_id, ':upload_id' => $upload_id]);
        
        if (!$checkStmt->fetch()) {
            // Add like
            $insertQuery = "INSERT INTO likes (user_id, upload_id) VALUES (:user_id, :upload_id)";
            $insertStmt = $db->prepare($insertQuery);
            $insertStmt->execute([':user_id' => $user_id, ':upload_id' => $upload_id]);
            
            // Update total likes count
            $updateQuery = "UPDATE user_uploads SET total_likes = total_likes + 1 WHERE upload_id = :upload_id";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->execute([':upload_id' => $upload_id]);
            
            // Update user's total likes received
            $updateUserQuery = "UPDATE users u 
                               JOIN user_uploads uu ON u.user_id = uu.user_id 
                               SET u.total_likes_received = u.total_likes_received + 1 
                               WHERE uu.upload_id = :upload_id";
            $updateUserStmt = $db->prepare($updateUserQuery);
            $updateUserStmt->execute([':upload_id' => $upload_id]);
            
            $response['message'] = 'Liked!';
        } else {
            $response['message'] = 'Already liked';
        }
    } else if ($action === 'unlike') {
        // Remove like
        $deleteQuery = "DELETE FROM likes WHERE user_id = :user_id AND upload_id = :upload_id";
        $deleteStmt = $db->prepare($deleteQuery);
        $deleteStmt->execute([':user_id' => $user_id, ':upload_id' => $upload_id]);
        
        // Update total likes count
        $updateQuery = "UPDATE user_uploads SET total_likes = total_likes - 1 WHERE upload_id = :upload_id AND total_likes > 0";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->execute([':upload_id' => $upload_id]);
        
        // Update user's total likes received
        $updateUserQuery = "UPDATE users u 
                           JOIN user_uploads uu ON u.user_id = uu.user_id 
                           SET u.total_likes_received = u.total_likes_received - 1 
                           WHERE uu.upload_id = :upload_id AND u.total_likes_received > 0";
        $updateUserStmt = $db->prepare($updateUserQuery);
        $updateUserStmt->execute([':upload_id' => $upload_id]);
        
        $response['message'] = 'Like removed';
    }
    
    // Get updated like count
    $countQuery = "SELECT total_likes FROM user_uploads WHERE upload_id = :upload_id";
    $countStmt = $db->prepare($countQuery);
    $countStmt->execute([':upload_id' => $upload_id]);
    $likes = $countStmt->fetch(PDO::FETCH_ASSOC);
    
    $response['success'] = true;
    $response['likes'] = $likes ? (int)$likes['total_likes'] : 0;
    
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
?>