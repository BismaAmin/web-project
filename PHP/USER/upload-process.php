<?php
session_start();
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

$response = ['success' => false, 'message' => ''];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Please login first';
    echo json_encode($response);
    exit();
}

// Check if POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Use POST method';
    echo json_encode($response);
    exit();
}

// Get form data
$dish_name = trim($_POST['dish_name'] ?? '');
$description = trim($_POST['description'] ?? '');
$recipe_id = !empty($_POST['recipe_id']) ? $_POST['recipe_id'] : null;
$user_id = $_SESSION['user_id'];

if (empty($dish_name)) {
    $response['message'] = 'Please enter a dish name';
    echo json_encode($response);
    exit();
}

// Handle image upload
$image_path = null;
if (isset($_FILES['dish_image']) && $_FILES['dish_image']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = __DIR__ . '/../uploads/dishes/';
    
    // Create directory if not exists
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($_FILES['dish_image']['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (!in_array($file_extension, $allowed_extensions)) {
        $response['message'] = 'Only JPG, PNG, GIF, WEBP files are allowed';
        echo json_encode($response);
        exit();
    }
    
    $filename = uniqid() . '_' . time() . '.' . $file_extension;
    $target_path = $upload_dir . $filename;
    
    if (move_uploaded_file($_FILES['dish_image']['tmp_name'], $target_path)) {
        $image_path = 'uploads/dishes/' . $filename;
    }
}

// Database connection
require_once __DIR__ . '/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        $response['message'] = 'Database connection failed';
        echo json_encode($response);
        exit();
    }
    
    // Insert into database
    $query = "INSERT INTO user_uploads (user_id, recipe_id, dish_name, description, image_path, created_at) 
              VALUES (:user_id, :recipe_id, :dish_name, :description, :image_path, NOW())";
    
    $stmt = $db->prepare($query);
    $result = $stmt->execute([
        ':user_id' => $user_id,
        ':recipe_id' => $recipe_id,
        ':dish_name' => $dish_name,
        ':description' => $description,
        ':image_path' => $image_path
    ]);
    
    if ($result) {
        $upload_id = $db->lastInsertId();
        
        // Update user's total uploads count
        $updateQuery = "UPDATE users SET total_uploads = total_uploads + 1 WHERE user_id = :user_id";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->execute([':user_id' => $user_id]);
        
        $response['success'] = true;
        $response['message'] = 'Dish uploaded successfully!';
        $response['upload_id'] = $upload_id;
    } else {
        $response['message'] = 'Failed to save to database';
    }
    
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
exit();
?>