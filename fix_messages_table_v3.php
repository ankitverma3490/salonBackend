<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "Updating messages table schema...\n";

    $stmt = $db->query("DESCRIBE messages");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Rename recipient_id to receiver_id if it exists
    if (in_array('recipient_id', $columns)) {
        echo "Renaming recipient_id to receiver_id...\n";
        $db->exec("ALTER TABLE messages CHANGE COLUMN recipient_id receiver_id varchar(36) NOT NULL");
    }

    // Rename message to content if it exists
    if (in_array('message', $columns)) {
        echo "Renaming message to content...\n";
        $db->exec("ALTER TABLE messages CHANGE COLUMN message content text");
    }

    // Add salon_id if it doesn't exist
    if (!in_array('salon_id', $columns)) {
        echo "Adding salon_id column...\n";
        $db->exec("ALTER TABLE messages ADD COLUMN salon_id varchar(36) AFTER receiver_id");
    }

    // Add recipient_type if it doesn't exist
    if (!in_array('recipient_type', $columns)) {
        echo "Adding recipient_type column...\n";
        $db->exec("ALTER TABLE messages ADD COLUMN recipient_type varchar(50) DEFAULT 'staff' AFTER content");
    }

    echo "Adding indexes...\n";
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
