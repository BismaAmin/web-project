<?php
header('Content-Type: application/json');

require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    $users      = $db->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn();
    $recipes    = $db->query("SELECT COUNT(*) FROM recipes")->fetchColumn();
    $uploads    = $db->query("SELECT COUNT(*) FROM user_uploads")->fetchColumn();
    $challenges = $db->query("SELECT COUNT(*) FROM challenges WHERE is_active = 1")->fetchColumn();

    echo json_encode([
        'success'    => true,
        'users'      => (int)$users,
        'recipes'    => (int)$recipes,
        'uploads'    => (int)$uploads,
        'challenges' => (int)$challenges
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false]);
}
?>
