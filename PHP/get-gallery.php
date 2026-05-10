<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/config/database.php';

$response = ['success' => false, 'data' => [], 'message' => ''];

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        $response['message'] = 'Database connection failed';
        echo json_encode($response);
        exit();
    }
    
    // Get all uploads with user information
    $query = "SELECT 
                u.upload_id as id,
                u.dish_name as dish,
                u.description as caption,
                u.created_at,
                u.total_likes as likes,
                u.image_path,
                us.username,
                us.full_name,
                us.profile_picture
              FROM user_uploads u
              JOIN users us ON u.user_id = us.user_id
              WHERE us.is_active = 1
              ORDER BY u.created_at DESC";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $uploads = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the data for the gallery
    $gallery_items = [];
    foreach ($uploads as $upload) {
        // Calculate time ago
        $time_ago = time_ago($upload['created_at']);
        
        // Build the correct image path
        $image_path = null;
        if (!empty($upload['image_path'])) {
            $clean_path = ltrim($upload['image_path'], '/');
            // Accept any path that starts with uploads/
            if (strpos($clean_path, 'uploads/') === 0) {
                $image_path = $clean_path;
            } else {
                $image_path = 'uploads/dishes/' . $clean_path;
            }
        }
        
        $gallery_items[] = [
            'id'         => $upload['id'],
            'image_path' => $image_path,
            'username'   => $upload['username'],
            'dish'       => $upload['dish'],
            'likes'      => (int)$upload['likes'],
            'caption'    => $upload['caption'] ?: 'A delicious homemade dish.',
            'time'       => $time_ago
        ];
    }
    
    $response['success'] = true;
    $response['data']    = $gallery_items;
    $response['count']   = count($gallery_items);
    
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);

function time_ago($timestamp) {
    $time_ago        = strtotime($timestamp);
    $current_time    = time();
    $time_difference = $current_time - $time_ago;

    $seconds = $time_difference;
    $minutes = round($seconds / 60);
    $hours   = round($seconds / 3600);
    $days    = round($seconds / 86400);
    $weeks   = round($seconds / 604800);

    if ($seconds <= 60) {
        return "Just now";
    } elseif ($minutes <= 60) {
        return $minutes == 1 ? "1 minute ago" : "$minutes minutes ago";
    } elseif ($hours <= 24) {
        return $hours == 1 ? "1 hour ago" : "$hours hours ago";
    } elseif ($days <= 7) {
        return $days == 1 ? "yesterday" : "$days days ago";
    } elseif ($weeks <= 4.3) {
        return $weeks == 1 ? "1 week ago" : "$weeks weeks ago";
    } else {
        return date("M j, Y", $time_ago);
    }
}
?>
