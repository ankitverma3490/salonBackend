<?php
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();

    // Add brand, discount, and stock_quantity to platform_products
    $columnsToAdd = [
        'brand' => "VARCHAR(100) NULL AFTER category",
        'discount' => "DECIMAL(5,2) DEFAULT 0.00 AFTER price",
        'stock_quantity' => "INT DEFAULT 0 AFTER discount"
    ];

    foreach ($columnsToAdd as $col => $definition) {
        try {
            $db->exec("ALTER TABLE platform_products ADD COLUMN $col $definition");
            echo "Column '$col' added successfully.\n";
        } catch (Exception $e) {
            echo "Column '$col' might already exist or failed: " . $e->getMessage() . "\n";
        }
    }

} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
