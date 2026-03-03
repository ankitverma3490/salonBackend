<?php
/**
 * 🛠️ Migration: Add Payment Details to Salons Table
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();

    $sql = "ALTER TABLE salons 
            ADD COLUMN upi_id VARCHAR(255) DEFAULT NULL AFTER gst_number,
            ADD COLUMN bank_details TEXT DEFAULT NULL AFTER upi_id";

    $db->exec($sql);

    echo "✅ Migration successful: Added upi_id and bank_details to salons table.\n";
} catch (PDOException $e) {
    if ($e->getCode() == '42S21') {
        echo "ℹ️ Columns already exist. Skipping migration.\n";
    } else {
        echo "❌ Migration failed: " . $e->getMessage() . "\n";
    }
}
