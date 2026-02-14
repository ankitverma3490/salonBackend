<?php
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();

    // 1. Add staff_id to bookings table
    try {
        $db->exec("ALTER TABLE bookings ADD COLUMN staff_id VARCHAR(36) NULL AFTER service_id");
        $db->exec("ALTER TABLE bookings ADD CONSTRAINT fk_bookings_staff FOREIGN KEY (staff_id) REFERENCES staff_profiles(id) ON DELETE SET NULL");
        echo "Successfully added staff_id to bookings table.\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "Column staff_id already exists in bookings table.\n";
        } else {
            throw $e;
        }
    }

    // 2. Create staff_services table
    $db->exec("CREATE TABLE IF NOT EXISTS staff_services (
        id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
        staff_id VARCHAR(36) NOT NULL,
        service_id VARCHAR(36) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (staff_id) REFERENCES staff_profiles(id) ON DELETE CASCADE,
        FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
        UNIQUE KEY unique_staff_service (staff_id, service_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "Successfully created/verified staff_services table.\n";

} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
