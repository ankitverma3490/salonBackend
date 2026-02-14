<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();

    $tables = ['permissions', 'role_permissions', 'user_roles', 'staff_profiles'];
    foreach ($tables as $table) {
        $stmt = $db->query("SELECT COUNT(*) FROM `$table`");
        echo "$table Count: " . $stmt->fetchColumn() . "\n";
    }
}
catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
