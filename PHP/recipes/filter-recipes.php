<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$difficulty = $_GET['difficulty'] ?? 'all';
$maxTime = $_GET['max_time'] ?? null;
$ingredients = $_GET['ingredients'] ?? '';

$query = "SELECT * FROM recipes WHERE 1=1";
$params = [];

if ($difficulty !== 'all') {
    $query .= " AND difficulty = :difficulty";
    $params[':difficulty'] = $difficulty;
}

if ($maxTime) {
    $query .= " AND cooking_time <= :max_time";
    $params[':max_time'] = $maxTime;
}

$query .= " ORDER BY total_likes DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'count' => count($recipes),
    'recipes' => $recipes
]);
?>