<?php
require_once __DIR__ . '/backend/config.php';
require_once __DIR__ . '/backend/Database.php';

$db = Database::getInstance()->getConnection();
$salonId = 'd1de11a6-76ed-4e1d-8ec4-65e54841231c';

$stmt = $db->prepare("SELECT * FROM salon_offers WHERE salon_id = ?");
$stmt->execute([$salonId]);
$offers = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Offers for $salonId:\n";
print_r($offers);
?>