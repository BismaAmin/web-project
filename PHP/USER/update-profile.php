<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$full_name = trim($_POST['full_name'] ?? '');
$username  = trim($_POST['username']  ?? '');
$email     = trim($_POST['email']     ?? '');
$bio       = trim($_POST['bio']       ?? '');

if (empty($full_name) || empty($username) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Required fields missing']);
    exit();
}

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

$check = $db->prepare("SELECT user_id FROM users WHERE username = :username AND user_id != :user_id");
$check->execute([':username' => $username, ':user_id' => $_SESSION['user_id']]);
if ($check->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Username already taken']);
    exit();
}

// ── Handle profile picture upload ───────────────────────────────────────────
$pic_path = null; // null = no change

if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $file    = $_FILES['profile_picture'];
    $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','gif','webp'];

    if (!in_array($ext, $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Invalid image type. Allowed: jpg, png, gif, webp']);
        exit();
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'Image too large (max 5 MB)']);
        exit();
    }

    $uploadDir = dirname(__DIR__, 2) . '/uploads/profile-pictures/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = 'pp_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
    $dest     = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        echo json_encode(['success' => false, 'message' => 'Failed to save profile picture']);
        exit();
    }

    // Delete old picture
    $old = $db->prepare("SELECT profile_picture FROM users WHERE user_id = :id");
    $old->execute([':id' => $_SESSION['user_id']]);
    $oldRow = $old->fetch(PDO::FETCH_ASSOC);
    if ($oldRow && $oldRow['profile_picture'] && file_exists($uploadDir . $oldRow['profile_picture'])) {
        @unlink($uploadDir . $oldRow['profile_picture']);
    }

    $pic_path = $filename;
}
// ────────────────────────────────────────────────────────────────────────────

if ($pic_path !== null) {
    $update = $db->prepare("UPDATE users SET full_name=:fn, username=:un, email=:em, bio=:bio, profile_picture=:pic WHERE user_id=:id");
    $result = $update->execute([
        ':fn'  => $full_name, ':un' => $username,
        ':em'  => $email,     ':bio'=> $bio,
        ':pic' => $pic_path,  ':id' => $_SESSION['user_id']
    ]);
} else {
    $update = $db->prepare("UPDATE users SET full_name=:fn, username=:un, email=:em, bio=:bio WHERE user_id=:id");
    $result = $update->execute([
        ':fn' => $full_name, ':un' => $username,
        ':em' => $email,     ':bio'=> $bio,
        ':id' => $_SESSION['user_id']
    ]);
}

if ($result) {
    // Update session variables
    $_SESSION['username']  = $username;
    $_SESSION['full_name'] = $full_name;
    $_SESSION['email']     = $email;
    if ($pic_path) {
        $_SESSION['profile_picture'] = $pic_path;
    }
    
    echo json_encode([
        'success'         => true,
        'message'         => 'Profile updated',
        'profile_picture' => $pic_path,
        'username'        => $username,
        'full_name'       => $full_name,
        'email'           => $email
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Update failed']);
}
?>