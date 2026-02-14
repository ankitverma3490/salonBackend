<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

$db = Database::getInstance()->getConnection();

function checkTable($db, $tableName)
{
    echo "=== {$tableName} Table ===\n";
    try {
        $stmt = $db->query("DESCRIBE `{$tableName}`");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "  {$row['Field']} ({$row['Type']})\n";
        }
    }
    catch (Exception $e) {
        echo "  Error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

$tables = [
    'salons',
    'services',
    'bookings',
    'staff_profiles',
    'subscription_plans',
    'salon_subscriptions',
    'notifications',
    'users',
    'profiles'
];

foreach ($tables as $table) {
    checkTable($db, $table);
}
