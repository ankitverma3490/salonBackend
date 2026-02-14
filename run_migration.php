<?php
// Script to apply database migration for nullable service_id
require_once __DIR__ . '/config.php';

try {
    $db = Database::getInstance()->getConnection();

    // Check if column is already nullable
    $stmt = $db->prepare("SELECT IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'bookings' AND COLUMN_NAME = 'service_id'");
    $stmt->execute();
    $result = $stmt->fetch();

    if ($result && $result['IS_NULLABLE'] === 'YES') {
        echo "The column 'service_id' in table 'bookings' is already nullable.\n";
        exit(0);
    }

    echo "Applying migration: Making 'service_id' nullable in 'bookings' table...\n";
    $sql = "ALTER TABLE bookings MODIFY service_id VARCHAR(36) NULL";
    $db->exec($sql);

    echo "Migration successful!\n";
}
catch (Exception $e) {
    echo "Error applying migration: " . $e->getMessage() . "\n";
    exit(1);
}
