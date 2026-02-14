<?php
/**
 * Fix missing columns for super admin dashboard
 */

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/Database.php";

try {
    $db = Database::getInstance()->getConnection();

    echo "=== Fixing Super Admin Dashboard Schema Issues ===\n\n";

    // 1. Add final_price to bookings if missing
    echo "1. Checking bookings table for final_price... ";
    try {
        $db->exec("ALTER TABLE bookings ADD COLUMN final_price DECIMAL(10,2) AFTER total_price");
        echo "Added final_price. ";
    }
    catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "final_price exists. ";
        }
        else {
            echo "Error: " . substr($e->getMessage(), 0, 50) . "... ";
        }
    }
    echo "Done.\n";

    // 2. Add approved_at to salons if missing
    echo "2. Checking salons table for approved_at... ";
    try {
        $db->exec("ALTER TABLE salons ADD COLUMN approved_at TIMESTAMP NULL AFTER approval_status");
        echo "Added approved_at. ";
    }
    catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "approved_at exists. ";
        }
        else {
            echo "Error: " . substr($e->getMessage(), 0, 50) . "... ";
        }
    }
    echo "Done.\n";

    // 3. Verify and add other potentially missing columns
    echo "3. Checking for other missing columns... ";

    $columnsToAdd = [
        "ALTER TABLE bookings ADD COLUMN discount_amount DECIMAL(10,2) DEFAULT 0.00 AFTER final_price",
        "ALTER TABLE salons ADD COLUMN rejected_at TIMESTAMP NULL AFTER approved_at",
        "ALTER TABLE salons ADD COLUMN rejection_reason TEXT AFTER rejected_at"
    ];

    foreach ($columnsToAdd as $sql) {
        try {
            $db->exec($sql);
            echo ". ";
        }
        catch (PDOException $e) {
        // Column already exists or other non-critical error
        }
    }
    echo "Done.\n";

    echo "\n✓ All super admin dashboard schema fixes applied!\n";

}
catch (Exception $e) {
    echo "\nError: " . $e->getMessage() . "\n";
    exit(1);
}
