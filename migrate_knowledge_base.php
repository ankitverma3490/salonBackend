<?php
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();

    // Create salon_knowledge_base table
    $sql = "CREATE TABLE IF NOT EXISTS salon_knowledge_base (
        id VARCHAR(36) PRIMARY KEY,
        salon_id VARCHAR(36) NOT NULL,
        category VARCHAR(50) NOT NULL DEFAULT 'Skin Care',
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $db->exec($sql);
    echo "Table 'salon_knowledge_base' created successfully.\n";

} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
