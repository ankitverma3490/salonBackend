<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

function columnExists($db, $table, $column)
{
    $stmt = $db->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
    $stmt->execute([$column]);
    return $stmt->fetch() !== false;
}

try {
    $db = Database::getInstance()->getConnection();
    echo "Updating messages table schema...\n";

    // Rename recipient_id to receiver_id if it exists
    if (columnExists($db, 'messages', 'recipient_id')) {
        echo "Renaming recipient_id to receiver_id...\n";
        $db->exec("ALTER TABLE messages CHANGE COLUMN recipient_id receiver_id varchar(36) NOT NULL");
    }

    // Rename message to content if it exists
    if (columnExists($db, 'messages', 'message')) {
        echo "Renaming message to content...\n";
        $db->exec("ALTER TABLE messages CHANGE COLUMN message content text");
    }

    // Add salon_id if it doesn't exist
    if (!columnExists($db, 'messages', 'salon_id')) {
        echo "Adding salon_id column...\n";
        $db->exec("ALTER TABLE messages ADD COLUMN salon_id varchar(36) AFTER receiver_id");
    }

    // Add recipient_type if it doesn't exist
    if (!columnExists($db, 'messages', 'recipient_type')) {
        echo "Adding recipient_type column...\n";
        $db->exec("ALTER TABLE messages ADD COLUMN recipient_type varchar(50) DEFAULT 'staff' AFTER content");
    }

    echo "Adding indexes...\n";
    // Using simple ADD INDEX as primary optimization
    try {
        $db->exec("ALTER TABLE messages ADD INDEX idx_messages_salon_read (salon_id, is_read)");
    }
    catch (Exception $e) {
    }

    try {
        $db->exec("ALTER TABLE messages ADD INDEX idx_messages_receiver_read (receiver_id, is_read)");
    }
    catch (Exception $e) {
    }

    echo "Messages table schema updated successfully.\n";
}
catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
