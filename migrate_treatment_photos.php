<?php
require_once __DIR__ . '/Database.php';

echo "Adding photo columns to treatment_records...\n";

try {
    $db = Database::getInstance()->getConnection();
    $sql = file_get_contents(__DIR__ . '/add_treatment_photos.sql');
    $db->exec($sql);
    echo "✓ Photo columns added successfully!\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
