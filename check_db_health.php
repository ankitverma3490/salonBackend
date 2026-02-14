<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();

    echo "--- Bookings Indexes ---\n";
    $stmt = $db->query("SHOW INDEX FROM bookings");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

    echo "\n--- Notifications Indexes ---\n";
    $stmt = $db->query("SHOW INDEX FROM notifications");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

    echo "\n--- Table Status ---\n";
    $stmt = $db->query("SHOW TABLE STATUS WHERE Name IN ('bookings', 'notifications')");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

}
catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
