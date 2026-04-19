<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Check if column exists
    $stmt = $db->query("SHOW COLUMNS FROM `services` LIKE 'is_featured'");
    $columnExists = $stmt->fetch();
    
    if (!$columnExists) {
        $db->exec("ALTER TABLE `services` ADD COLUMN `is_featured` BOOLEAN DEFAULT FALSE");
        echo "Successfully added 'is_featured' column to services table.\n";
    } else {
        echo "Column 'is_featured' already exists.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
