<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Use POST method';
    echo json_encode($response);
    exit();
}

$full_name = trim($_POST['full_name'] ?? '');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($full_name) || empty($username) || empty($email) || empty($password)) {
    $response['message'] = 'All fields are required';
    echo json_encode($response);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Invalid email format';
    echo json_encode($response);
    exit();
}

if (strlen($password) < 6) {
    $response['message'] = 'Password must be at least 6 characters';
    echo json_encode($response);
    exit();
}

if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
    $response['message'] = 'Username must be 3-20 characters (letters, numbers, underscore)';
    echo json_encode($response);
    exit();
}

require_once __DIR__ . '/../config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        $response['message'] = 'Database connection failed';
        echo json_encode($response);
        exit();
    }

    $checkQuery = "SELECT user_id FROM users WHERE username = :username OR email = :email";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([':username' => $username, ':email' => $email]);

    if ($checkStmt->fetch()) {
        $response['message'] = 'Username or email already exists';
        echo json_encode($response);
        exit();
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // FIX: include role column with default value 'user'
    $insertQuery = "INSERT INTO users (full_name, username, email, password_hash, role, is_active, created_at)
                    VALUES (:full_name, :username, :email, :password_hash, 'user', 1, NOW())";

    $insertStmt = $db->prepare($insertQuery);
    $result = $insertStmt->execute([
        ':full_name' => $full_name,
        ':username' => $username,
        ':email' => $email,
        ':password_hash' => $password_hash
    ]);

    if ($result) {
        $response['success'] = true;
        $response['message'] = 'Account created successfully! Please login.';
    } else {
        $response['message'] = 'Failed to create account. Please try again.';
    }

} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
exit();
?>
