<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "Altering staff_attendance table...\n";
    $db->exec("ALTER TABLE staff_attendance MODIFY COLUMN date DATE NOT NULL DEFAULT (CURRENT_DATE)");
    echo "Success!\n";
}
catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
