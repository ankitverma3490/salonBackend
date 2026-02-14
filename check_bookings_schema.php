<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

$db = Database::getInstance()->getConnection();
$stmt = $db->query('DESCRIBE bookings');
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Bookings table columns:\n";
foreach ($columns as $col) {
    echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
}
