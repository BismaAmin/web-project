<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$ingredient_id   = $_POST['ingredient_id']   ?? 0;
$ingredient_name = trim($_POST['ingredient_name'] ?? '');
$category        = $_POST['category']        ?? 'Vegetables';
$current_image   = trim($_POST['current_image']   ?? '');

if (empty($ingredient_name)) {
    echo json_encode(['success' => false, 'message' => 'Ingredient name required']);
    exit();
}

// ── Handle image upload ──────────────────────────────────────────────────────
$image_path = $current_image; // keep existing image by default

if (isset($_FILES['ingredient_image']) && $_FILES['ingredient_image']['error'] === UPLOAD_ERR_OK) {
    $file    = $_FILES['ingredient_image'];
    $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'jfif'];

    if (!in_array($ext, $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Invalid image type. Allowed: jpg, png, gif, webp']);
        exit();
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'Image too large (max 5 MB)']);
        exit();
    }

    $uploadDir = dirname(__DIR__, 2) . '/uploads/ingredient-images/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = uniqid('ing_', true) . '.' . $ext;
    $dest     = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        echo json_encode(['success' => false, 'message' => 'Failed to save image']);
        exit();
    }

    // Delete old image if replaced
    if ($current_image && file_exists($uploadDir . $current_image)) {
        @unlink($uploadDir . $current_image);
    }

    $image_path = $filename;
}
// ────────────────────────────────────────────────────────────────────────────

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();
$db->exec("SET NAMES utf8mb4");

if ($ingredient_id > 0) {
    $sql  = "UPDATE ingredients SET ingredient_name=:name, category=:cat, image_path=:img WHERE ingredient_id=:id";
    $stmt = $db->prepare($sql);
    $result = $stmt->execute([':name' => $ingredient_name, ':cat' => $category, ':img' => $image_path, ':id' => $ingredient_id]);
} else {
    $sql  = "INSERT INTO ingredients (ingredient_name, category, image_path) VALUES (:name, :cat, :img)";
    $stmt = $db->prepare($sql);
    $result = $stmt->execute([':name' => $ingredient_name, ':cat' => $category, ':img' => $image_path]);
}

echo json_encode(['success' => (bool)$result, 'message' => $result ? 'Ingredient saved' : 'DB error', 'image_path' => $image_path]);
?>
