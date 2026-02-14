<?php
/**
 * Database Optimization Script
 * Adds indexes to frequently joined/filtered columns to prevent timeouts.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "=== Database Optimization ===\n\n";

    $tablesToOptimize = [
        'bookings' => ['salon_id', 'user_id', 'service_id', 'staff_id', 'booking_date'],
        'notifications' => ['user_id'],
        'services' => ['salon_id'],
        'staff_profiles' => ['salon_id', 'user_id'],
        'user_roles' => ['user_id', 'salon_id'],
        'profiles' => ['user_id']
    ];

    foreach ($tablesToOptimize as $table => $columns) {
        echo "Optimizing table: $table\n";

        // Get existing indexes
        $stmt = $db->query("SHOW INDEX FROM $table");
        $existingIndexes = $stmt->fetchAll(PDO::FETCH_COLUMN, 2); // Column index for Key_name is 2

        foreach ($columns as $column) {
            $indexName = "idx_{$table}_{$column}";

            if (in_array($indexName, $existingIndexes)) {
                echo "  - Index $indexName already exists. ✓\n";
                continue;
            }

            echo "  - Adding index for $column... ";
            try {
                $db->exec("ALTER TABLE $table ADD INDEX $indexName ($column)");
                echo "✓\n";
            }
            catch (Exception $e) {
                echo "Failed: " . $e->getMessage() . "\n";
            }
        }
        echo "\n";
    }

    echo "=== Optimization Complete ===\n";

}
catch (Exception $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
}
