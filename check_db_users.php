<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/config.php';

try {
    $db = Database::getInstance()->getConnection();

    echo "=== DB CONFIG ===\n";
    echo "Host: " . DB_HOST . "\n";
    echo "DB: " . DB_NAME . "\n";
    echo "Port: " . DB_PORT . "\n";
    echo "User: " . DB_USER . "\n";

    echo "\n=== USERS ===\n";
    $stmt = $db->query("SELECT id, email FROM users LIMIT 10");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['id'] . " | " . $row['email'] . "\n";
    }

}
catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
