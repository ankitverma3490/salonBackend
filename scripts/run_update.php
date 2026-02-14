<?php
require_once __DIR__ . '/backend/config.php';
require_once __DIR__ . '/backend/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    $sql = file_get_contents(__DIR__ . '/backend/reminders_update.sql');
    $db->exec($sql);
    echo "SQL executed successfully\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
