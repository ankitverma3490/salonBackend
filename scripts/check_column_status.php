<?php
require_once __DIR__ . '/backend/config.php';
require_once __DIR__ . '/backend/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "Database connection successful.\n";

    $stmt = $db->query("SHOW FULL COLUMNS FROM salons WHERE Field = 'id'");
    $col = $stmt->fetch(PDO::FETCH_ASSOC);
    print_r($col);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
