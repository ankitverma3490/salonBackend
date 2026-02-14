<?php
/**
 * Direct Data Transfer from Local to Railway
 * This script connects to both databases and transfers data directly
 * bypassing SQL export/import issues
 */

error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once __DIR__ . "/config.php";

// Local database configuration
$localHost = 'localhost';
$localPort = '3306';
$localDbName = 'salon_booking';
$localUser = 'root';
$localPass = '';

echo "=== Direct Data Transfer Tool ===\n\n";

try {
    // Connect to local database
    $localDsn = "mysql:host={$localHost};port={$localPort};dbname={$localDbName};charset=utf8mb4";
    echo "Connecting to local database: {$localDbName}...\n";
    $localDb = new PDO($localDsn, $localUser, $localPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "✓ Connected to local database\n\n";

    // Connect to Railway database
    $railwayDsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    echo "Connecting to Railway database: " . DB_NAME . "...\n";
    $railwayDb = new PDO($railwayDsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "✓ Connected to Railway database\n\n";

    // Get all tables from local database
    $stmt = $localDb->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "Found " . count($tables) . " tables in local database\n\n";

    // Define table order to respect foreign key constraints
    $orderedTables = [
        'users',
        'profiles',
        'platform_admins',
        'subscription_plans',
        'subscription_addons',
        'salons',
        'user_roles',
        'services',
        'staff_profiles',
        'staff_services',
        'staff_leaves',
        'staff_attendance',
        'bookings',
        'booking_reviews',
        'customer_salon_profiles',
        'treatment_records',
        'notifications',
        'messages',
        'reminders',
        'salon_subscriptions',
        'salon_offers',
        'salon_inventory',
        'salon_knowledge_base',
        'salon_suppliers',
        'salon_addons',
        'platform_settings',
        'platform_banners',
        'platform_offers',
        'platform_payments',
        'platform_products',
        'platform_orders',
        'loyalty_programs',
        'loyalty_rewards',
        'loyalty_transactions',
        'coin_transactions',
        'customer_product_purchases',
        'contact_enquiries',
        'newsletter_subscribers',
        'password_resets',
        'permissions',
        'role_permissions',
        'staff_specific_permissions',
        'admin_activity_logs'
    ];

    $transferred = 0;
    $totalRows = 0;

    foreach ($orderedTables as $table) {
        if (!in_array($table, $tables)) {
            echo "Skipping {$table} (not in local database)\n";
            continue;
        }

        echo "Processing table: {$table}... ";

        // Check if table exists in Railway
        try {
            $railwayDb->query("SELECT 1 FROM `{$table}` LIMIT 1");
        }
        catch (PDOException $e) {
            echo "✗ (table doesn't exist in Railway)\n";
            continue;
        }

        // Get row count
        $countStmt = $localDb->query("SELECT COUNT(*) FROM `{$table}`");
        $rowCount = $countStmt->fetchColumn();

        if ($rowCount == 0) {
            echo "✓ (empty)\n";
            continue;
        }

        // Clear existing data in Railway table
        try {
            $railwayDb->exec("SET FOREIGN_KEY_CHECKS=0");
            $railwayDb->exec("TRUNCATE TABLE `{$table}`");
            $railwayDb->exec("SET FOREIGN_KEY_CHECKS=1");
        }
        catch (PDOException $e) {
            echo "Warning: Could not truncate table: " . $e->getMessage() . "\n";
        }

        // Fetch all data from local
        $dataStmt = $localDb->query("SELECT * FROM `{$table}`");
        $rows = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            echo "✓ (empty)\n";
            continue;
        }

        // Get column names
        $columns = array_keys($rows[0]);
        $columnList = '`' . implode('`, `', $columns) . '`';
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));

        // Prepare insert statement
        $insertSql = "INSERT INTO `{$table}` ({$columnList}) VALUES ({$placeholders})";
        $insertStmt = $railwayDb->prepare($insertSql);

        // Insert rows in batches
        $batchSize = 100;
        $inserted = 0;

        foreach (array_chunk($rows, $batchSize) as $batch) {
            $railwayDb->beginTransaction();

            foreach ($batch as $row) {
                try {
                    $insertStmt->execute(array_values($row));
                    $inserted++;
                }
                catch (PDOException $e) {
                    echo "\n  Error inserting row: " . $e->getMessage() . "\n";
                }
            }

            $railwayDb->commit();
        }

        echo "✓ ({$inserted}/{$rowCount} rows)\n";
        $transferred++;
        $totalRows += $inserted;
    }

    echo "\n=== Transfer Complete! ===\n";
    echo "Tables transferred: {$transferred}\n";
    echo "Total rows: {$totalRows}\n\n";
    echo "Next step: Run verify_users.php to check user roles\n";


}
catch (PDOException $e) {
    echo "\n✗ Database Error: " . $e->getMessage() . "\n";
    exit(1);
}
catch (Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
