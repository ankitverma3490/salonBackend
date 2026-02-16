<?php
/**
 * Migration: Add missing columns to salon_offers table
 */

error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../Database.php";

try {
    $db = Database::getInstance()->getConnection();

    echo "=== Patching salon_offers table ===\n\n";

    // Columns to add
    $columnsToAdd = [
        "code VARCHAR(50) AFTER description",
        "type ENUM('percentage', 'fixed') DEFAULT 'percentage' AFTER code",
        "value DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER type",
        "max_usage INT DEFAULT NULL AFTER value",
        "status ENUM('active', 'inactive', 'expired') DEFAULT 'active' AFTER end_date"
    ];

    // Check existing columns first to avoid errors
    $stmt = $db->query("DESCRIBE salon_offers");
    $existingColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($columnsToAdd as $colDef) {
        $colName = explode(' ', $colDef)[0];
        if (!in_array($colName, $existingColumns)) {
            echo "Adding column: $colName... ";
            $db->exec("ALTER TABLE salon_offers ADD COLUMN $colDef");
            echo "✓\n";
        }
        else {
            echo "Column $colName already exists. Skipping.\n";
        }
    }

    // Also check for name mismatch (discount_type vs type, discount_value vs value)
    // The API uses 'type' and 'value', but create_missing_tables.php used 'discount_type' and 'discount_value'
    if (in_array('discount_type', $existingColumns) && !in_array('type', $existingColumns)) {
        echo "Renaming discount_type to type... ";
        $db->exec("ALTER TABLE salon_offers CHANGE discount_type type ENUM('percentage', 'fixed') DEFAULT 'percentage'");
        echo "✓\n";
    }

    if (in_array('discount_value', $existingColumns) && !in_array('value', $existingColumns)) {
        echo "Renaming discount_value to value... ";
        $db->exec("ALTER TABLE salon_offers CHANGE discount_value value DECIMAL(10,2) NOT NULL DEFAULT 0.00");
        echo "✓\n";
    }

    echo "\n✓ Patch completed successfully!\n";

}
catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
