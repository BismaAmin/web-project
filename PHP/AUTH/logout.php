<?php
/**
 * Logout Process
 * Sizzle & Share - Interactive Food Recipe Challenge Platform
 */

require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/functions.php';

// Log activity if user was logged in
if (isLoggedIn()) {
    $userId = $_SESSION['user_id'];
    logActivity($userId, 'logout', 'User logged out');
    
    // Clear remember me token from database
    if (isset($_COOKIE['remember_token'])) {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM user_sessions WHERE session_token = ?");
        $stmt->execute([$_COOKIE['remember_token']]);
        setcookie('remember_token', '', time() - 3600, '/');
    }
}

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Set flash message for next request
session_start();
setFlashMessage('success', 'You have been successfully logged out.');

// Redirect to home page
redirect('index.html');
?>