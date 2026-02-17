<?php
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/Database.php";
$db = Database::getInstance()->getConnection();

echo "--- SALON OFFERS DATA ---\n";
$stmt = $db->query("SELECT id, salon_id, code, status, start_date, end_date FROM salon_offers");
$offers = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($offers);

echo "\n--- RAW QUERY TEST (UPPER code) ---\n";
$testCode = isset($offers[0]['code']) ? strtoupper($offers[0]['code']) : 'NONE';
$testSalon = isset($offers[0]['salon_id']) ? $offers[0]['salon_id'] : 'NONE';
echo "Testing with Code: $testCode, Salon: $testSalon\n";

$stmt = $db->prepare("SELECT * FROM salon_offers WHERE salon_id = ? AND UPPER(code) = ?");
$stmt->execute([$testSalon, $testCode]);
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
