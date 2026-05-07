<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// User growth (last 30 days vs previous 30 days)
$last_month = $db->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();
$prev_month = $db->query("SELECT COUNT(*) FROM users WHERE created_at BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();
$growth = $prev_month > 0 ? round(($last_month - $prev_month) / $prev_month * 100) : ($last_month > 0 ? 100 : 0);

// Most popular recipe
$popular = $db->query("SELECT recipe_name, total_likes FROM recipes ORDER BY total_likes DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

// Top chef (user with most uploads)
$top_chef = $db->query("SELECT username, total_uploads FROM users WHERE role = 'user' ORDER BY total_uploads DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

// Monthly stats - users + uploads + likes per month
$monthly = $db->query("
    SELECT
        DATE_FORMAT(u.created_at, '%Y-%m') as month,
        COUNT(DISTINCT u.user_id) as new_users,
        COALESCE(up.new_uploads, 0) as new_uploads,
        COALESCE(up.total_likes, 0) as total_likes
    FROM users u
    LEFT JOIN (
        SELECT
            DATE_FORMAT(created_at, '%Y-%m') as up_month,
            COUNT(*) as new_uploads,
            SUM(total_likes) as total_likes
        FROM user_uploads
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ) up ON DATE_FORMAT(u.created_at, '%Y-%m') = up.up_month
    WHERE u.created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(u.created_at, '%Y-%m')
    ORDER BY month DESC
")->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'user_growth' => $growth,
    'most_popular_recipe' => $popular ? $popular['recipe_name'] : 'N/A',
    'top_chef' => $top_chef ? $top_chef['username'] : 'N/A',
    'monthly_stats' => $monthly
]);
?>
