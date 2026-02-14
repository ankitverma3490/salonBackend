<?php
/**
 * Fix Salons Table - Add All Missing Columns
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();

    echo "=== Fixing Salons Table ===\n\n";

    // Get current columns
    $stmt = $db->query("DESCRIBE salons");
    $existingCols = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "Existing columns:\n";
    foreach ($existingCols as $col) {
        echo "  - {$col}\n";
    }
    echo "\n";

    $columnsToAdd = [
        'category' => "VARCHAR(100) AFTER description",
        'target_audience' => "VARCHAR(255) AFTER category",
        'rating' => "DECIMAL(3,2) DEFAULT 0.00 AFTER target_audience",
        'total_reviews' => "INT DEFAULT 0 AFTER rating",
    ];

    $added = 0;

    foreach ($columnsToAdd as $colName => $definition) {
        if (!in_array($colName, $existingCols)) {
            echo "Adding {$colName}... ";
            try {
                $db->exec("ALTER TABLE salons ADD COLUMN {$colName} {$definition}");
                echo "✓\n";
                $added++;
            }
            catch (PDOException $e) {
                echo "✗ Error: " . substr($e->getMessage(), 0, 100) . "\n";
            }
        }
        else {
            echo "{$colName} already exists ✓\n";
        }
    }

    echo "\n=== Final Structure ===\n";
    $stmt = $db->query("DESCRIBE salons");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  {$row['Field']} - {$row['Type']}\n";
    }

    echo "\n✓ Salons table updated! Added {$added} columns.\n";


}
catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
