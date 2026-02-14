<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "--- Table: staff_attendance ---\n";
    $cols = $db->query("DESCRIBE staff_attendance")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $col) {
        echo "Field: {$col['Field']}, Type: {$col['Type']}, Null: {$col['Null']}, Default: " . ($col['Default'] ?? 'NULL') . "\n";
    }
}
catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
