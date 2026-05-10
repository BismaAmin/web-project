<?php
session_start();
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

$response = ['success' => false, 'message' => '', 'redirect' => '', 'role' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Use POST method';
    echo json_encode($response);
    exit();
}

$login_input = trim($_POST['login_input'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($login_input) || empty($password)) {
    $response['message'] = 'Please fill all fields';
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
    
    // Get user with role
    $query = "SELECT user_id, username, email, full_name, password_hash, role, is_active 
              FROM users 
              WHERE (email = :login_input OR username = :login_input) AND is_active = 1";
    
    $stmt = $db->prepare($query);
    $stmt->execute([':login_input' => $login_input]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        
        // Set redirect based on role
        if ($user['role'] === 'admin') {
            $response['redirect'] = 'admin/dashboard.html';
            $response['role'] = 'admin';
        } else {
            $response['redirect'] = 'user/dashboard.html';
            $response['role'] = 'user';
        }
        
        $response['success'] = true;
        $response['message'] = 'Login successful! Redirecting...';
        
    } else {
        $response['message'] = 'Invalid username/email or password';
    }
    
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
exit();
?>