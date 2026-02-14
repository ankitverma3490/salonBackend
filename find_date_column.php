<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    $tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        $stmt = $db->query("DESCRIBE `$table` LIKE 'date'");
        if ($stmt->fetch()) {
            echo "Table: $table has column 'date'\n";
            $full = $db->query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_ASSOC);
            print_r($full);
        }
    }
}
catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
