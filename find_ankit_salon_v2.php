<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT * FROM salons WHERE name LIKE '%Ankit%'");
    $salons = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($salons, JSON_PRETTY_PRINT);
}
catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
