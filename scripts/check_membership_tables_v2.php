<?php
require_once 'backend/Database.php';
$db = Database::getInstance()->getConnection();

$tables = ['subscription_plans', 'salon_subscriptions', 'salons', 'newsletter_subscribers'];

foreach ($tables as $table) {
    echo "TABLE: $table\n";
    try {
        $stmt = $db->query("DESC $table");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "  {$row['Field']} ({$row['Type']})\n";
        }
    } catch (Exception $e) {
        echo "  ERROR: " . $e->getMessage() . "\n";
    }
}
