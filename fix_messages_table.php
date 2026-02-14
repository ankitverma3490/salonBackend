<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "Updating messages table schema...\n";

    // Rename columns if they exist as old names
    $db->exec("ALTER TABLE messages CHANGE COLUMN recipient_id receiver_id varchar(36) NOT NULL");
    $db->exec("ALTER TABLE messages CHANGE COLUMN message content text");

    // Add missing columns if they don't exist
    $db->exec("ALTER TABLE messages ADD COLUMN IF NOT EXISTS salon_id varchar(36) AFTER receiver_id");
    $db->exec("ALTER TABLE messages ADD COLUMN IF NOT EXISTS recipient_type varchar(50) DEFAULT 'staff' AFTER content");

    // Add indexes now that columns exist
    $db->exec("ALTER TABLE messages ADD INDEX IF NOT EXISTS idx_messages_salon_read (salon_id, is_read)");
    $db->exec("ALTER TABLE messages ADD INDEX IF NOT EXISTS idx_messages_receiver_read (receiver_id, is_read)");

    echo "Messages table schema updated successfully.\n";
}
catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
