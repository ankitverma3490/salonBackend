<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "--- staff_specific_permissions ---\n";
    $stmt = $db->query("DESCRIBE staff_specific_permissions");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

    echo "\n--- permissions ---\n";
    $stmt = $db->query("DESCRIBE permissions");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
}
catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
