<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();

    echo "Adding missing indexes to notifications table...\n";

    // Check if index exists before adding
    $stmt = $db->query("SHOW INDEX FROM notifications WHERE Key_name = 'idx_notifications_user_id'");
    if (!$stmt->fetch()) {
        echo "Creating idx_notifications_user_id...\n";
        $db->exec("CREATE INDEX idx_notifications_user_id ON notifications(user_id)");
    }

    $stmt = $db->query("SHOW INDEX FROM notifications WHERE Key_name = 'idx_notifications_salon_id'");
    if (!$stmt->fetch()) {
        echo "Creating idx_notifications_salon_id...\n";
        $db->exec("CREATE INDEX idx_notifications_salon_id ON notifications(salon_id)");
    }

    echo "Done.\n";
}
catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
