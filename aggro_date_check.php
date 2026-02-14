<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    $tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        $stmt = $db->query("SHOW COLUMNS FROM `$table` LIKE 'date'");
        if ($res = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "MATCH FOUND: Table $table | Column {$res['Field']} | Null {$res['Null']} | Default " . ($res['Default'] ?? 'NULL') . "\n";
        }
    }
}
catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
