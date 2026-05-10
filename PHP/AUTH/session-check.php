<?php
/**
 * Session Check - Verify if user is logged in
 * Sizzle & Share - Interactive Food Recipe Challenge Platform
 */

require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/functions.php';

header('Content-Type: application/json');

$response = ['logged_in' => false];

if (isLoggedIn()) {
    $user = getCurrentUser();
    if ($user) {
        $response['logged_in'] = true;
        $response['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'full_name' => $user['full_name'],
            'avatar' => $user['avatar']
        ];
    } else {
        // User ID in session but not found in database
        session_destroy();
    }
}

echo json_encode($response);
?>