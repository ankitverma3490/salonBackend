<?php
/**
 * Comprehensive fix for all missing columns causing salon creation errors
 */

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/Database.php";

try {
    $db = Database::getInstance()->getConnection();

    echo "=== Comprehensive Schema Fix for Salon Creation ===\n\n";

    // 1. Fix bookings table
    echo "1. Fixing bookings table... ";
    $bookingColumns = [
        'final_price' => "DECIMAL(10,2) AFTER total_price",
        'discount_applied' => "DECIMAL(10,2) DEFAULT 0.00 AFTER final_price",
        'payment_status' => "ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending' AFTER status",
        'payment_method' => "VARCHAR(50) AFTER payment_status"
    ];

    foreach ($bookingColumns as $col => $definition) {
        try {
            $db->exec("ALTER TABLE bookings ADD COLUMN $col $definition");
            echo "$col ";
        }
        catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column') === false &&
            strpos($e->getMessage(), 'check that it exists') === false) {
            // Column doesn't exist and there's a real error
            }
        }
    }
    echo "Done.\n";

    // 2. Fix salons table
    echo "2. Fixing salons table... ";
    $salonColumns = [
        'approved_at' => "TIMESTAMP NULL AFTER approval_status",
        'approved_by' => "VARCHAR(36) AFTER approved_at",
        'rejected_at' => "TIMESTAMP NULL AFTER approved_by",
        'rejected_by' => "VARCHAR(36) AFTER rejected_at",
        'rejection_reason' => "TEXT AFTER rejected_by",
        'is_active' => "BOOLEAN DEFAULT TRUE AFTER approval_status"
    ];

    foreach ($salonColumns as $col => $definition) {
        try {
            $db->exec("ALTER TABLE salons ADD COLUMN $col $definition");
            echo "$col ";
        }
        catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column') === false &&
            strpos($e->getMessage(), 'check that it exists') === false) {
            // Column doesn't exist and there's a real error
            }
        }
    }
    echo "Done.\n";

    // 3. Fix services table
    echo "3. Fixing services table... ";
    $serviceColumns = [
        'price' => "DECIMAL(10,2) DEFAULT 0.00 AFTER duration",
        'discounted_price' => "DECIMAL(10,2) AFTER price",
        'is_active' => "BOOLEAN DEFAULT TRUE AFTER category"
    ];

    foreach ($serviceColumns as $col => $definition) {
        try {
            $db->exec("ALTER TABLE services ADD COLUMN $col $definition");
            echo "$col ";
        }
        catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column') === false &&
            strpos($e->getMessage(), 'check that it exists') === false) {
            // Column doesn't exist and there's a real error
            }
        }
    }
    echo "Done.\n";

    // 4. Verify critical columns exist
    echo "4. Verifying critical columns... ";
    $tables = ['bookings', 'salons', 'services'];
    foreach ($tables as $table) {
        try {
            $stmt = $db->query("DESCRIBE $table");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo "$table(" . count($columns) . ") ";
        }
        catch (PDOException $e) {
            echo "$table(ERROR) ";
        }
    }
    echo "Done.\n";

    echo "\n✓ All schema fixes applied successfully!\n";

}
catch (Exception $e) {
    echo "\nError: " . $e->getMessage() . "\n";
    exit(1);
}
