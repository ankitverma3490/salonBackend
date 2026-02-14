<?php
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();

    // Add service_id column to salon_knowledge_base
    $sql = "ALTER TABLE salon_knowledge_base ADD COLUMN service_id VARCHAR(36) NULL AFTER salon_id";
    $db->exec($sql);

    // Add foreign key constraint
    $sql = "ALTER TABLE salon_knowledge_base ADD CONSTRAINT fk_knowledge_base_service FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE";
    $db->exec($sql);

    echo "Table 'salon_knowledge_base' updated with 'service_id' column.\n";

} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column 'service_id' already exists.\n";
    } else {
        echo "Migration failed: " . $e->getMessage() . "\n";
    }
}
