<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();

    $sql = "CREATE TABLE IF NOT EXISTS platform_orders (
        id VARCHAR(36) PRIMARY KEY,
        user_id VARCHAR(36) NULL,
        guest_name VARCHAR(255) NULL,
        guest_email VARCHAR(255) NULL,
        items JSON,
        shipping_address JSON,
        total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        status ENUM('placed', 'dispatched', 'delivered', 'cancelled') DEFAULT 'placed',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $db->exec($sql);
    echo "Table 'platform_orders' created successfully.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
