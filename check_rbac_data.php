<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT COUNT(*) FROM staff_specific_permissions");
    echo "Count: " . $stmt->fetchColumn() . "\n";
}
catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
