<?php
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/Database.php";
$db = Database::getInstance()->getConnection();
$stm = $db->query("SHOW TABLES");
$tables = $stm->fetchAll(PDO::FETCH_COLUMN);
echo "Total Tables: " . count($tables) . "\n";
print_r($tables);
if (in_array("contact_enquiries", $tables)) {
    echo "\n'contact_enquiries' exists.\n";
} else {
    echo "\n'contact_enquiries' is MISSING.\n";
}
