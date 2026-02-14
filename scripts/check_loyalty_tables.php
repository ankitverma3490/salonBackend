<?php
require_once __DIR__ . '/backend/config.php';
require_once __DIR__ . '/backend/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "Database connection successful.\n";

    echo "Checking tables...\n";
    $tablesToCheck = ['users', 'salons', 'customer_salon_profiles'];

    foreach ($tablesToCheck as $table) {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "Table '$table' exists. Describing:\n";
            $stmt = $db->query("DESCRIBE $table");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($columns as $col) {
                echo " - " . $col['Field'] . " (" . $col['Type'] . ")\n";
            }
        } else {
            echo "Table '$table' DOES NOT EXIST.\n";
        }
        echo "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
