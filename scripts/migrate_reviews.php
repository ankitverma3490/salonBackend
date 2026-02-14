<?php
require_once __DIR__ . '/backend/config.php';
require_once __DIR__ . '/backend/Database.php';

try {
    $db = Database::getInstance()->getConnection();

    $sql = "CREATE TABLE IF NOT EXISTS booking_reviews (
        id VARCHAR(36) PRIMARY KEY,
        booking_id VARCHAR(36) NOT NULL UNIQUE,
        user_id VARCHAR(36) NOT NULL,
        salon_id VARCHAR(36) NOT NULL,
        rating INT DEFAULT 5,
        comment TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $db->exec($sql);
    echo "Table booking_reviews ensured successfully.\n";

    // Check if column exists in bookings for good measure or just verify structure
    $stmt = $db->query("SHOW TABLES LIKE 'booking_reviews'");
    if ($stmt->fetch()) {
        echo "Verification: Table exists in registry.\n";
    } else {
        echo "Verification: Table MISSING after creation attempt.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
