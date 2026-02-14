<?php
require_once __DIR__ . '/config.php';

echo "Adding blood_group column to staff_profiles...\n";

try {
    $sql = file_get_contents(__DIR__ . '/add_blood_group_column.sql');
    $pdo->exec($sql);
    echo "✓ blood_group column added successfully!\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
