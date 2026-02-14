<?php
/**
 * Schema Fix v3 - Adding Missing Subscription Plan Price Columns
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "=== Starting Schema Fix v3 ===\n\n";

    // 1. subscription_plans
    $stmt = $db->query("DESCRIBE subscription_plans");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('price_monthly', $cols)) {
        echo "Adding price_monthly... ";
        $db->exec("ALTER TABLE subscription_plans ADD COLUMN price_monthly DECIMAL(10,2) DEFAULT 0 AFTER price");
        echo "✓\n";
    }
    if (!in_array('price_yearly', $cols)) {
        echo "Adding price_yearly... ";
        $db->exec("ALTER TABLE subscription_plans ADD COLUMN price_yearly DECIMAL(10,2) DEFAULT 0 AFTER price_monthly");
        echo "✓\n";
    }

    echo "=== Schema Fix Complete ===\n";

}
catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
