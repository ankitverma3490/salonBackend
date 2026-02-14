<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "Reverting staff_attendance table schema...\n";

    // 1. Drop the redundant date column
    echo "Dropping 'date' column...\n";
    try {
        $db->exec("ALTER TABLE staff_attendance DROP COLUMN date");
    }
    catch (PDOException $e) {
        echo "Note: 'date' column might not exist or already dropped: " . $e->getMessage() . "\n";
    }

    // 2. Change check_in to TIMESTAMP
    echo "Changing 'check_in' to TIMESTAMP...\n";
    $db->exec("ALTER TABLE staff_attendance MODIFY COLUMN check_in TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");

    // 3. Change check_out to TIMESTAMP
    echo "Changing 'check_out' to TIMESTAMP...\n";
    $db->exec("ALTER TABLE staff_attendance MODIFY COLUMN check_out TIMESTAMP NULL DEFAULT NULL");

    echo "Successfully reverted schema!\n";
}
catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
