<?php
require 'backend/Database.php';
try {
    $db = Database::getInstance()->getConnection();
    $db->exec("CREATE TABLE IF NOT EXISTS newsletter_subscribers (
        id INT AUTO_INCREMENT PRIMARY KEY, 
        email VARCHAR(255) NOT NULL UNIQUE, 
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo 'Newsletter table created successfully';
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
