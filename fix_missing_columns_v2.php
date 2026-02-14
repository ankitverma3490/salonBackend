<?php
/**
 * Fix Missing Columns in Platform Products and Booking Reviews
 */

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/Database.php";

try {
    $db = Database::getInstance()->getConnection();

    echo "=== Fixing Missing Columns ===\n\n";

    // 1. Fix platform_products
    echo "Updating platform_products... ";
    $columns = [
        'discount' => "DECIMAL(10,2) DEFAULT 0.00 AFTER price",
        'stock_quantity' => "INT DEFAULT 0 AFTER discount",
        'image_public_id' => "VARCHAR(255) AFTER image_url",
        'image_url_2' => "TEXT AFTER image_public_id",
        'image_2_public_id' => "VARCHAR(255) AFTER image_url_2",
        'image_url_3' => "TEXT AFTER image_2_public_id",
        'image_3_public_id' => "VARCHAR(255) AFTER image_url_3",
        'image_url_4' => "TEXT AFTER image_3_public_id",
        'image_4_public_id' => "VARCHAR(255) AFTER image_url_4",
        'brand' => "VARCHAR(100) AFTER category",
        'target_audience' => "ENUM('customer', 'salon', 'both') DEFAULT 'both' AFTER brand",
        'features' => "TEXT AFTER target_audience"
    ];

    foreach ($columns as $col => $definition) {
        try {
            $db->exec("ALTER TABLE platform_products ADD COLUMN $col $definition");
            echo "Added $col. ";
        }
        catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            // echo "$col exists. ";
            }
            else {
                echo "Error adding $col: " . $e->getMessage() . ". ";
            }
        }
    }
    echo "Done.\n";

    // 2. Fix booking_reviews
    echo "Updating booking_reviews... ";
    try {
        $db->exec("ALTER TABLE booking_reviews ADD COLUMN user_id VARCHAR(36) AFTER booking_id");
        $db->exec("ALTER TABLE booking_reviews ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE");
        echo "Added user_id. Done.\n";
    }
    catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "user_id exists. Done.\n";
        }
        else {
            echo "Error adding user_id: " . $e->getMessage() . ".\n";
        }
    }

    echo "\n✓ Fixes applied successfully!\n";

}
catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
