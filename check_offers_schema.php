<?php
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/Database.php";
$db = Database::getInstance()->getConnection();
$stmt = $db->query("DESCRIBE salon_offers");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
