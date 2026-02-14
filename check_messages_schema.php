<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "--- messages schema ---\n";
    $stmt = $db->query("DESCRIBE messages");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
}
catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
