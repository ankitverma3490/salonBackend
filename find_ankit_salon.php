<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT s.name as salon_name, u.email, p.full_name FROM salons s JOIN users u ON s.owner_id = u.id JOIN profiles p ON u.id = p.user_id WHERE s.name LIKE '%Ankit%'");
    $salons = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($salons, JSON_PRETTY_PRINT);
}
catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
