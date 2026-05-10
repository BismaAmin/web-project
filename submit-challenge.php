<?php
header('Content-Type: application/json');
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

//post take form data from browser
$challenge_id = $_POST['challenge_id'] ?? 0;
$upload_id = $_POST['upload_id'] ?? 0;

// Check if already submitted
$checkQuery = "SELECT * FROM challenge_participants WHERE challenge_id = :challenge_id AND user_id = :user_id";
$checkStmt = $db->prepare($checkQuery);
$checkStmt->execute([
    ':challenge_id' => $challenge_id,
    ':user_id' => $_SESSION['user_id']
]);

//already submitted
if ($checkStmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Already submitted']);
    exit();
}

// Insert submission
$query = "INSERT INTO 
challenge_participants (challenge_id, user_id, upload_id) 
VALUES (:challenge_id, :user_id, :upload_id)";
$stmt = $db->prepare($query);

//databse mein entry save 
$result = $stmt->execute([
    ':challenge_id' => $challenge_id,
    ':user_id' => $_SESSION['user_id'],
    ':upload_id' => $upload_id
]);

if ($result) {
    // Update user stats
    $updateQuery = "UPDATE users SET total_challenges_joined = total_challenges_joined + 1 WHERE user_id = :user_id";
    $updateStmt = $db->prepare($updateQuery);
    $updateStmt->execute([':user_id' => $_SESSION['user_id']]);
    
    echo json_encode(['success' => true, 'message' => 'Challenge submission successful!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Submission failed']);
}
?>