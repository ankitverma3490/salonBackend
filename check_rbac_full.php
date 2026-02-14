<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

function checkTable($db, $tableName)
{
    try {
        echo "--- Checking $tableName ---\n";
        $stmt = $db->query("DESCRIBE `$tableName`");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($columns, JSON_PRETTY_PRINT) . "\n";
    }
    catch (Exception $e) {
        echo "Error checking $tableName: " . $e->getMessage() . "\n";
    }
}

try {
    $db = Database::getInstance()->getConnection();
    checkTable($db, 'staff_specific_permissions');
    checkTable($db, 'permissions');
    checkTable($db, 'role_permissions');
    checkTable($db, 'user_roles');
}
catch (Exception $e) {
    echo "Global Error: " . $e->getMessage() . "\n";
}
