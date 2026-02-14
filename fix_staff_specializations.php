<?php
/**
 * Final Schema Fix for Staff Table
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "=== Fix Staff Table Schema ===\n\n";

    // Check if specialties exists
    $stmt = $db->query("DESCRIBE staff_profiles");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (in_array('specialties', $cols) && !in_array('specializations', $cols)) {
        echo "Renaming 'specialties' to 'specializations'... ";
        $db->exec("ALTER TABLE staff_profiles CHANGE specialties specializations TEXT");
        echo "✓\n";
    }
    elseif (!in_array('specializations', $cols)) {
        echo "Adding 'specializations'... ";
        $db->exec("ALTER TABLE staff_profiles ADD COLUMN specializations TEXT AFTER bio");
        echo "✓\n";
    }
    else {
        echo "'specializations' already exists. ✓\n";
    }

    echo "=== Schema Fix Complete ===\n";

}
catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
