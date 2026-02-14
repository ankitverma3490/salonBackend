<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();

    // Create salon_inventory table
    $sql = "CREATE TABLE IF NOT EXISTS salon_inventory (
        id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
        salon_id VARCHAR(36) NOT NULL,
        name VARCHAR(255) NOT NULL,
        category VARCHAR(100),
        stock_quantity INT DEFAULT 0,
        min_stock_level INT DEFAULT 5,
        unit_price DECIMAL(10, 2) DEFAULT 0.00,
        supplier_name VARCHAR(255),
        last_restocked_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE,
        INDEX idx_salon_id (salon_id),
        INDEX idx_category (category)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $db->exec($sql);
    echo "Table 'salon_inventory' created successfully.\n";

    // Optional: Create salon_suppliers table if we want dynamic suppliers too
    $sql = "CREATE TABLE IF NOT EXISTS salon_suppliers (
        id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
        salon_id VARCHAR(36) NOT NULL,
        name VARCHAR(255) NOT NULL,
        contact_person VARCHAR(255),
        phone VARCHAR(20),
        email VARCHAR(255),
        address TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE,
        INDEX idx_salon_id (salon_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $db->exec($sql);
    echo "Table 'salon_suppliers' created successfully.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
