<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    $tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    $found = false;
    foreach ($tables as $table) {
        try {
            $cols = $db->query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($cols as $col) {
                if (strtolower($col['Field']) === 'date') {
                    echo "Table: $table | Column: {$col['Field']} | Null: {$col['Null']} | Default: " . ($col['Default'] === null ? 'NULL' : $col['Default']) . "\n";
                    $found = true;
                }
            }
        }
        catch (Exception $e) {
        // Skip non-existent tables or errors
        }
    }
    if (!$found)
        echo "No exact 'date' column found in any other tables.\n";
}
catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
