<?php
/**
 * Migration: Add usage_count column to salon_offers table
 */

error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../Database.php";

try {
    $db = Database::getInstance()->getConnection();

    echo "=== Adding usage_count to salon_offers table ===\n\n";

    // 1. Check if column exists
    $stmt = $db->query("DESCRIBE salon_offers");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('usage_count', $columns)) {
        echo "Adding usage_count column... ";
        $db->exec("ALTER TABLE salon_offers ADD COLUMN usage_count INT NOT NULL DEFAULT 0 AFTER max_usage");
        echo "✓\n";
    }
    else {
        echo "usage_count column already exists.\n";
    }

    // 2. Also ensure max_usage exists (just in case)
    if (!in_array('max_usage', $columns)) {
        echo "Adding max_usage column... ";
        $db->exec("ALTER TABLE salon_offers ADD COLUMN max_usage INT DEFAULT NULL AFTER value");
        echo "✓\n";
    }

    // 3. Ensure code/status etc exist (mirroring fix_offers_schema.php logic)
    if (!in_array('code', $columns)) {
        echo "Adding code column... ";
        $db->exec("ALTER TABLE salon_offers ADD COLUMN code VARCHAR(50) AFTER description");
        echo "✓\n";
    }

    if (!in_array('status', $columns)) {
        echo "Adding status column... ";
        $db->exec("ALTER TABLE salon_offers ADD COLUMN status ENUM('active', 'inactive', 'expired') DEFAULT 'active' AFTER end_date");
        echo "✓\n";
    }

    echo "\n✓ Migration completed successfully!\n";

}
catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
