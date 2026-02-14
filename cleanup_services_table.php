<?php
/**
 * Remove redundant duration column from services table
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "=== Dropping Redundant Duration Column ===\n\n";

    // 1. Check if duration exists
    $stmt = $db->query("DESCRIBE services");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (in_array('duration', $cols)) {
        echo "Dropping 'duration'... ";
        $db->exec("ALTER TABLE services DROP COLUMN duration");
        echo "✓\n";
    }
    else {
        echo "'duration' column not found. Already clean! ✓\n";
    }

    echo "=== Clean Up Complete ===\n";

}
catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
