<?php
/**
 * Ensure both specializations and specialties exist for safety
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "=== Sync Staff Table Schema ===\n\n";

    $stmt = $db->query("DESCRIBE staff_profiles");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('specializations', $cols)) {
        echo "Adding 'specializations'... ";
        $db->exec("ALTER TABLE staff_profiles ADD COLUMN specializations TEXT AFTER bio");
        echo "✓\n";
    }

    if (!in_array('specialties', $cols)) {
        echo "Adding 'specialties'... ";
        $db->exec("ALTER TABLE staff_profiles ADD COLUMN specialties TEXT AFTER specializations");
        echo "✓\n";
    }

    echo "=== Schema Sync Complete ===\n";

}
catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
