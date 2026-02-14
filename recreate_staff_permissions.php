<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();

    echo "Dropping existing staff_specific_permissions table...\n";
    $db->exec("DROP TABLE IF EXISTS staff_specific_permissions");

    echo "Creating staff_specific_permissions table with correct schema...\n";
    $db->exec("
        CREATE TABLE `staff_specific_permissions` (
            `staff_id` varchar(36) NOT NULL,
            `permission_id` varchar(36) NOT NULL,
            `is_allowed` tinyint(1) DEFAULT 1,
            PRIMARY KEY (`staff_id`, `permission_id`),
            KEY `permission_id` (`permission_id`),
            CONSTRAINT `staff_specific_permissions_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff_profiles` (`id`) ON DELETE CASCADE,
            CONSTRAINT `staff_specific_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    echo "Table recreated successfully.\n";
}
catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
