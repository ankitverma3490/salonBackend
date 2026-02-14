<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/Database.php";

try {
    $db = Database::getInstance()->getConnection();
    echo "Connected.\n";
    $sql = file_get_contents(__DIR__ . "/migrations/create_contact_enquiries.sql");
    $db->exec($sql);
    echo "SUCCESS: contact_enquiries created.\n";
} catch (Exception $e) {
    echo "FAILURE: " . $e->getMessage() . "\n";
}
