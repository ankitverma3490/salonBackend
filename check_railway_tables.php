<?php
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/Database.php";

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "Tables in Railway database (" . count($tables) . " total):\n";
    foreach ($tables as $table) {
        echo "  - {$table}\n";
    }
}
catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
