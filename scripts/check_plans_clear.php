<?php
require_once __DIR__ . '/../Database.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT name, price_monthly FROM subscription_plans");
while ($row = $stmt->fetch()) {
    echo $row['name'] . ": RM " . $row['price_monthly'] . "\n";
}
