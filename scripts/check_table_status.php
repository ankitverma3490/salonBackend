<?php
require_once __DIR__ . '/backend/config.php';
require_once __DIR__ . '/backend/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "Database connection successful.\n";

    echo "Status of 'salons' table:\n";
    $stmt = $db->query("SHOW TABLE STATUS LIKE 'salons'");
    $status = $stmt->fetch(PDO::FETCH_ASSOC);
    print_r($status);

    echo "\nStatus of 'users' table:\n";
    $stmt = $db->query("SHOW TABLE STATUS LIKE 'users'");
    $status = $stmt->fetch(PDO::FETCH_ASSOC);
    print_r($status);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
