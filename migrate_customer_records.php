<?php
require_once __DIR__ . '/config.php';

echo "Creating customer records tables...\n";

try {
    $sql = file_get_contents(__DIR__ . '/add_customer_records.sql');
    $pdo->exec($sql);
    echo "✓ Customer records tables created successfully!\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
