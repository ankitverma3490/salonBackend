<?php
require_once __DIR__ . '/../Database.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT id, name, price_monthly FROM subscription_plans");
$plans = $stmt->fetchAll();
echo json_encode($plans, JSON_PRETTY_PRINT);
