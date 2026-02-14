<?php
require_once __DIR__ . '/Database.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->query("DESCRIBE bookings");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
