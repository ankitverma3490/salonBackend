<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT * FROM role_permissions");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
}
catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
