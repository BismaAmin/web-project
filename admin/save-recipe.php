<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$recipe_id    = $_POST['recipe_id']    ?? 0;
$recipe_name  = trim($_POST['recipe_name']  ?? '');
$description  = trim($_POST['description']  ?? '');
$cooking_time = $_POST['cooking_time'] ?? null;
$difficulty   = $_POST['difficulty']   ?? 'Medium';
$instructions = trim($_POST['instructions'] ?? '');
$current_image = trim($_POST['current_image'] ?? '');

if (empty($recipe_name)) {
    echo json_encode(['success' => false, 'message' => 'Recipe name required']);
    exit();
}

// ── Handle image upload ──────────────────────────────────────────────────────
$image_path = $current_image; // keep existing image by default

if (isset($_FILES['recipe_image']) && $_FILES['recipe_image']['error'] === UPLOAD_ERR_OK) {
    $file     = $_FILES['recipe_image'];
    $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed  = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'jfif'];

    if (!in_array($ext, $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Invalid image type. Allowed: jpg, png, gif, webp']);
        exit();
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'Image too large (max 5 MB)']);
        exit();
    }

    $uploadDir = dirname(__DIR__, 2) . '/uploads/recipe-images/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = uniqid('recipe_', true) . '.' . $ext;
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

if ($recipe_id > 0) {
    $sql = "UPDATE recipes SET recipe_name=:name, description=:desc, cooking_time=:time,
            difficulty=:diff, instructions=:inst, image_path=:img WHERE recipe_id=:id";
    $stmt = $db->prepare($sql);
    $result = $stmt->execute([
        ':name' => $recipe_name, ':desc' => $description, ':time' => $cooking_time,
        ':diff' => $difficulty,  ':inst' => $instructions, ':img'  => $image_path,
        ':id'   => $recipe_id
    ]);
} else {
    $sql = "INSERT INTO recipes (recipe_name, description, cooking_time, difficulty, instructions, image_path)
            VALUES (:name, :desc, :time, :diff, :inst, :img)";
    $stmt = $db->prepare($sql);
    $result = $stmt->execute([
        ':name' => $recipe_name, ':desc' => $description, ':time' => $cooking_time,
        ':diff' => $difficulty,  ':inst' => $instructions, ':img'  => $image_path
    ]);
}

echo json_encode(['success' => (bool)$result, 'message' => $result ? 'Recipe saved' : 'DB error', 'image_path' => $image_path]);
?>
