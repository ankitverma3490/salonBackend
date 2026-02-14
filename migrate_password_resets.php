<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

echo "Creating password_resets table...\n";

try {
    $db = Database::getInstance()->getConnection();
    $sql = file_get_contents(__DIR__ . '/add_password_resets_table.sql');
    $db->exec($sql);
    echo "✓ Password resets table created successfully!\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
