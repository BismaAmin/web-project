<?php
/**
 * Constants Configuration File
 * Sizzle & Share - Interactive Food Recipe Challenge Platform
 */

// Site Configuration
define('SITE_NAME', 'Sizzle & Share');
define('SITE_URL', 'http://localhost/sizzle-and-share/'); // Change this to your actual URL
define('SITE_EMAIL', 'info@sizzleandshare.com');

// Paths
define('BASE_PATH', dirname(__DIR__, 3) . '/');
define('UPLOAD_PATH', BASE_PATH . 'uploads/');
define('PROFILE_UPLOAD_PATH', UPLOAD_PATH . 'profiles/');
define('DISH_UPLOAD_PATH', UPLOAD_PATH . 'dishes/');

// Session Configuration
define('SESSION_NAME', 'sizzle_share_session');
define('SESSION_LIFETIME', 7 * 24 * 60 * 60); // 7 days

// Security
define('PASSWORD_BCRYPT_COST', 12);
define('CSRF_TOKEN_NAME', 'csrf_token');

// Pagination
define('ITEMS_PER_PAGE', 12);

// File Upload Limits
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Timezone
date_default_timezone_set('Asia/Dubai'); // Set your timezone

// Error Reporting (Disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}
?>