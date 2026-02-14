<?php
/**
 * Add missing columns to staff_profiles table
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();

    echo "=== Fixing Staff_Profiles Table ===\n\n";

    // Get current columns
    $stmt = $db->query("DESCRIBE staff_profiles");
    $existingCols = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "Existing columns:\n";
    foreach ($existingCols as $col) {
        echo "  - {$col}\n";
    }
    echo "\n";

    $columnsToAdd = [
        'display_name' => "VARCHAR(255) AFTER user_id",
        'email' => "VARCHAR(255) AFTER display_name",
        'phone' => "VARCHAR(20) AFTER email",
        'commission_percentage' => "DECIMAL(5,2) DEFAULT 0 AFTER specialties",
        'avatar_url' => "TEXT AFTER commission_percentage",
        'created_by_id' => "VARCHAR(36) AFTER avatar_url",
    ];

    $added = 0;

    foreach ($columnsToAdd as $colName => $definition) {
        if (!in_array($colName, $existingCols)) {
            echo "Adding {$colName}... ";
            try {
                $db->exec("ALTER TABLE staff_profiles ADD COLUMN {$colName} {$definition}");
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
    $stmt = $db->query("DESCRIBE staff_profiles");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  {$row['Field']} - {$row['Type']}\n";
    }

    echo "\n✓ Staff_profiles table updated! Added {$added} columns.\n";


}
catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
