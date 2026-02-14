<?php
require_once 'config.php';
require_once 'Database.php';

try {
    $db = Database::getInstance()->getConnection();
    $sql = file_get_contents('add_customer_records.sql');

    // Split SQL by semicolon, but be careful with UUID() and other functions
    // Better to use a more robust way or just run the whole thing if the driver supports it
    // PDO::exec can run multiple statements if the driver allows

    $db->exec($sql);
    echo "Migration successful!\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
