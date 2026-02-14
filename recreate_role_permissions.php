<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();

    echo "Dropping existing role_permissions table...\n";
    $db->exec("DROP TABLE IF EXISTS role_permissions");

    echo "Creating role_permissions table with correct schema...\n";
    $db->exec("
        CREATE TABLE `role_permissions` (
            `role` varchar(50) NOT NULL,
            `permission_id` varchar(36) NOT NULL,
            PRIMARY KEY (`role`, `permission_id`),
            KEY `permission_id` (`permission_id`),
            CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    echo "Table recreated successfully.\n";
}
catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
