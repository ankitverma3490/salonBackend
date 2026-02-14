<?php
/**
 * Fix missing columns for admin stats and bookings
 */

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/Database.php";

try {
    $db = Database::getInstance()->getConnection();

    echo "=== Fixing Admin Stats Schema Issues ===\n\n";

    // 1. Add price_paid to bookings if missing
    echo "1. Checking bookings table... ";
    try {
        $db->exec("ALTER TABLE bookings ADD COLUMN price_paid DECIMAL(10,2) AFTER final_price");
        echo "Added price_paid. ";
    }
    catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "price_paid exists. ";
        }
        else {
            echo "Error: " . substr($e->getMessage(), 0, 50) . "... ";
        }
    }
    echo "Done.\n";

    // 2. Verify customer_product_purchases has total_amount
    echo "2. Verifying customer_product_purchases... ";
    try {
        $stmt = $db->query("DESCRIBE customer_product_purchases");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('total_amount', $columns)) {
            echo "total_amount exists. ";
        }
        else {
            echo "total_amount missing! ";
        }
    }
    catch (PDOException $e) {
        echo "Table might not exist. ";
    }
    echo "Done.\n";

    // 3. Add missing columns to services if needed
    echo "3. Checking services table... ";
    try {
        $db->exec("ALTER TABLE services ADD COLUMN price DECIMAL(10,2) DEFAULT 0.00 AFTER duration");
        echo "Added price. ";
    }
    catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "price exists. ";
        }
    }
    echo "Done.\n";

    echo "\n✓ Schema fixes applied!\n";

}
catch (Exception $e) {
    echo "\nError: " . $e->getMessage() . "\n";
    exit(1);
}
