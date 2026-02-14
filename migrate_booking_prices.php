<?php
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    // Check if columns exist first to be safe
    $db->exec("ALTER TABLE bookings ADD COLUMN price_paid DECIMAL(10,2) NULL AFTER service_id");
    $db->exec("ALTER TABLE bookings ADD COLUMN discount_amount DECIMAL(10,2) DEFAULT 0 AFTER price_paid");
    $db->exec("ALTER TABLE bookings ADD COLUMN coupon_code VARCHAR(50) NULL AFTER discount_amount");

    echo "Migration successful: Added price_paid, discount_amount, and coupon_code to bookings table.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Migration already applied or columns already exist.\n";
    } else {
        echo "Migration failed: " . $e->getMessage() . "\n";
    }
}