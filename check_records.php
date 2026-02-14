<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "--- Records in staff_attendance ---\n";
    $history = $db->query("SELECT * FROM staff_attendance ORDER BY check_in DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    print_r($history);
}
catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
