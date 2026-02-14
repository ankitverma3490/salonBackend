<?php
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "Connected to database successfully.\n";

    // Get all tables
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($tables)) {
        echo "No tables found in the database.\n";
        exit;
    }

    echo "Found " . count($tables) . " tables: " . implode(", ", $tables) . "\n\n";

    foreach ($tables as $table) {
        echo "========================================\n";
        echo "TABLE: $table\n";
        echo "========================================\n";

        // Get columns
        $stmt = $db->query("DESCRIBE `$table`");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "Columns: " . implode(", ", $columns) . "\n";

        // Get row count
        $stmt = $db->query("SELECT COUNT(*) FROM `$table`");
        $count = $stmt->fetchColumn();
        echo "Total Rows: $count\n";

        if ($count > 0) {
            // Get data (limit 50)
            $stmt = $db->query("SELECT * FROM `$table` LIMIT 50");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo "Data (First 50 rows):\n";
            foreach ($rows as $index => $row) {
                echo "[" . ($index + 1) . "] ";
                // Compact print for readability
                echo json_encode($row, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                echo "\n";
            }
        } else {
            echo "No data in table.\n";
        }
        echo "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
