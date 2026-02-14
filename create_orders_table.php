<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=salon_booking', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "
    CREATE TABLE IF NOT EXISTS platform_orders (
        id CHAR(36) PRIMARY KEY,
        user_id CHAR(36) NOT NULL,
        guest_email VARCHAR(255),
        guest_name VARCHAR(255),
        total_amount DECIMAL(10, 2) NOT NULL,
        status ENUM('placed', 'dispatched', 'delivered', 'cancelled') DEFAULT 'placed',
        items JSON NOT NULL,
        shipping_address JSON,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;
    ";

    $db->exec($sql);
    echo "Table 'platform_orders' created successfully.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
