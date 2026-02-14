<?php
require_once __DIR__ . '/../Database.php';
$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT name, features FROM subscription_plans");
while ($row = $stmt->fetch()) {
    echo "--- " . $row['name'] . " ---\n";
    echo $row['features'] . "\n\n";
}
