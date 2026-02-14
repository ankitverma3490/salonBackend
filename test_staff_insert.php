<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "Testing insert into staff_profiles...\n";

    $id = 'test-' . time();
    $stmt = $db->prepare("INSERT INTO staff_profiles (id, user_id, salon_id, display_name, specializations) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$id, 'test-user', 'test-salon', 'Test Staff', '[]']);

    echo "Insert successful! ✓\n";

    // Clean up
    $db->exec("DELETE FROM staff_profiles WHERE id = '$id'");
    echo "Cleanup successful! ✓\n";


}
catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
