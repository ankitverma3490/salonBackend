<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    $tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        echo "--- Table: $table ---\n";
        $cols = $db->query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $col) {
            echo "Field: {$col['Field']}, Type: {$col['Type']}, Null: {$col['Null']}, Default: " . ($col['Default'] ?? 'NULL') . "\n";
        }
    }
}
catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
