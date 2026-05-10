<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$query = "SELECT * FROM challenges WHERE is_active = 1 
AND end_date >= CURDATE() 
ORDER BY end_date ASC LIMIT 1";
$stmt = $db->prepare($query);
$stmt->execute();
$challenge = $stmt->fetch(PDO::FETCH_ASSOC);

if ($challenge) {
    // Get participant count
    $countQuery = "SELECT COUNT(*) 
    as count 
    FROM challenge_participants 
    WHERE challenge_id = :challenge_id";
    $countStmt = $db->prepare($countQuery);
    $countStmt->execute([':challenge_id' => $challenge['challenge_id']]);
    $participantCount = $countStmt->fetch(PDO::FETCH_ASSOC);
    
    $challenge['participant_count'] = $participantCount['count'];
}

echo json_encode([
    'success' => true,
    'challenge' => $challenge
]);
?>