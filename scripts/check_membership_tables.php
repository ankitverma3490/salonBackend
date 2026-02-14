<?php
require_once 'backend/Database.php';
$db = Database::getInstance()->getConnection();

function checkTable($db, $tableName)
{
    echo "--- Table: $tableName ---\n";
    try {
        $stmt = $db->query("DESC $tableName");
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

checkTable($db, 'subscription_plans');
checkTable($db, 'salon_subscriptions');
checkTable($db, 'salons');
