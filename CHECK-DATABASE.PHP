<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Database Location Check</h1>";

require_once 'php/config/database.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("<p style='color:red'>Database connection failed!</p>");
}

echo "<p style='color:green'>✅ Connected to MySQL</p>";

// Show current database
$result = $db->query("SELECT DATABASE() as current_db");
$row = $result->fetch(PDO::FETCH_ASSOC);
echo "<h3>📌 Current Database: <span style='color:blue'>" . $row['current_db'] . "</span></h3>";

// Show all databases
echo "<h3>📊 All Databases on Server:</h3>";
$databases = $db->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
echo "<ul>";
foreach ($databases as $db_name) {
    echo "<li>" . $db_name . "</li>";
}
echo "</ul>";

// Show tables in current database
echo "<h3>📋 Tables in '" . $row['current_db'] . "':</h3>";
$tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
if (count($tables) > 0) {
    echo "<ul>";
    foreach ($tables as $table) {
        // Get row count for each table
        $count = $db->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        echo "<li><strong>$table</strong> - $count rows</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color:red'>No tables found in this database!</p>";
}

// Show users from ALL possible user tables
echo "<h3>👥 Finding Users in All Tables:</h3>";

// Check for users in current database
$possible_tables = ['users', 'user', 'accounts', 'members', 'user_accounts'];

foreach ($possible_tables as $table) {
    try {
        $check = $db->query("SHOW TABLES LIKE '$table'");
        if ($check->rowCount() > 0) {
            echo "<h4>Table: $table</h4>";
            $users = $db->query("SELECT * FROM `$table` LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
            if (count($users) > 0) {
                echo "<table border='1' cellpadding='8'>";
                echo "<tr>";
                foreach (array_keys($users[0]) as $col) {
                    echo "<th>$col</th>";
                }
                echo "</tr>";
                foreach ($users as $user) {
                    echo "<tr>";
                    foreach ($user as $value) {
                        echo "<td>" . htmlspecialchars(substr($value, 0, 50)) . "</td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No rows in $table</p>";
            }
        }
    } catch (Exception $e) {
        // Skip
    }
}

// Also check the exact path of the database file (for SQLite)
echo "<h3>📁 MySQL Connection Info:</h3>";
echo "<pre>";
echo "Host: " . $db->getAttribute(PDO::ATTR_CONNECTION_STATUS) . "\n";
echo "Server Version: " . $db->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";
echo "</pre>";
?>