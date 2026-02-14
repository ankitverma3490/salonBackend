<?php
/**
 * Complete Railway Database Setup
 * This script will:
 * 1. Drop all existing tables in Railway
 * 2. Create all tables with Railway-compatible schema (no DEFAULT UUID)
 * 3. Transfer all data from local database with proper UUIDs
 */

error_reporting(E_ALL);
ini_set("display_errors", 1);
ini_set('max_execution_time', 600); // 10 minutes

require_once __DIR__ . "/config.php";

// Local database configuration
$localHost = 'localhost';
$localPort = '3306';
$localDbName = 'salon_booking';
$localUser = 'root';
$localPass = '';

echo "=== Complete Railway Database Setup ===\n\n";
echo "This will:\n";
echo "1. Drop ALL existing tables in Railway\n";
echo "2. Create fresh schema\n";
echo "3. Import all data from local database\n\n";

try {
    // Connect to local database
    $localDsn = "mysql:host={$localHost};port={$localPort};dbname={$localDbName};charset=utf8mb4";
    echo "Connecting to local database...\n";
    $localDb = new PDO($localDsn, $localUser, $localPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "✓ Connected to local database\n";

    // Connect to Railway database
    $railwayDsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    echo "Connecting to Railway database...\n";
    $railwayDb = new PDO($railwayDsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "✓ Connected to Railway database\n\n";

    // Step 1: Drop all existing tables
    echo "Step 1: Dropping all existing tables in Railway...\n";
    $railwayDb->exec("SET FOREIGN_KEY_CHECKS=0");

    $stmt = $railwayDb->query("SHOW TABLES");
    $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($existingTables as $table) {
        echo "  Dropping {$table}...\n";
        $railwayDb->exec("DROP TABLE IF EXISTS `{$table}`");
    }

    $railwayDb->exec("SET FOREIGN_KEY_CHECKS=1");
    echo "✓ All tables dropped\n\n";

    // Step 2: Read and execute the database schema
    echo "Step 2: Creating tables from database.sql...\n";
    $schemaFile = __DIR__ . '/database.sql';

    if (!file_exists($schemaFile)) {
        throw new Exception("Schema file not found: {$schemaFile}");
    }

    $schemaSql = file_get_contents($schemaFile);

    // Remove DEFAULT (UUID()) from the schema - Railway doesn't support it
    $schemaSql = preg_replace('/DEFAULT\s+\(UUID\(\)\)/i', '', $schemaSql);
    $schemaSql = preg_replace('/DEFAULT\s+uuid\(\)/i', '', $schemaSql);

    // Split into statements
    $statements = array_filter(array_map('trim', explode(';', $schemaSql)));

    $railwayDb->exec("SET FOREIGN_KEY_CHECKS=0");

    foreach ($statements as $statement) {
        if (empty($statement) || substr($statement, 0, 2) === '--') {
            continue;
        }

        // Skip DROP TABLE statements - we already dropped everything
        if (stripos($statement, 'DROP TABLE') !== false) {
            continue;
        }

        // Skip INSERT statements from the schema file
        if (stripos($statement, 'INSERT INTO') !== false) {
            continue;
        }

        try {
            $railwayDb->exec($statement);
        }
        catch (PDOException $e) {
            // Ignore "already exists" errors
            if (strpos($e->getMessage(), 'already exists') === false) {
                echo "  Warning: " . substr($e->getMessage(), 0, 100) . "...\n";
            }
        }
    }

    $railwayDb->exec("SET FOREIGN_KEY_CHECKS=1");
    echo "✓ Schema created\n\n";

    // Step 3: Transfer data
    echo "Step 3: Transferring data from local to Railway...\n\n";

    // Define table order to respect foreign keys
    $tableOrder = [
        'users',
        'profiles',
        'platform_admins',
        'subscription_plans',
        'subscription_addons',
        'platform_settings',
        'permissions',
        'salons',
        'user_roles',
        'role_permissions',
        'services',
        'staff_profiles',
        'staff_services',
        'staff_specific_permissions',
        'staff_leaves',
        'staff_attendance',
        'bookings',
        'booking_reviews',
        'customer_salon_profiles',
        'treatment_records',
        'loyalty_programs',
        'loyalty_rewards',
        'loyalty_transactions',
        'coin_transactions',
        'salon_subscriptions',
        'salon_offers',
        'salon_inventory',
        'salon_knowledge_base',
        'salon_suppliers',
        'salon_addons',
        'platform_banners',
        'platform_offers',
        'platform_payments',
        'platform_products',
        'platform_orders',
        'customer_product_purchases',
        'notifications',
        'messages',
        'reminders',
        'contact_enquiries',
        'newsletter_subscribers',
        'password_resets',
        'admin_activity_logs'
    ];

    $totalRows = 0;
    $railwayDb->exec("SET FOREIGN_KEY_CHECKS=0");

    foreach ($tableOrder as $table) {
        // Check if table exists in local
        try {
            $localDb->query("SELECT 1 FROM `{$table}` LIMIT 1");
        }
        catch (PDOException $e) {
            continue; // Table doesn't exist in local
        }

        // Check if table exists in Railway
        try {
            $railwayDb->query("SELECT 1 FROM `{$table}` LIMIT 1");
        }
        catch (PDOException $e) {
            echo "  Skipping {$table} (doesn't exist in Railway)\n";
            continue;
        }

        // Get row count
        $countStmt = $localDb->query("SELECT COUNT(*) FROM `{$table}`");
        $rowCount = $countStmt->fetchColumn();

        if ($rowCount == 0) {
            echo "  {$table}: empty\n";
            continue;
        }

        echo "  {$table}: transferring {$rowCount} rows... ";

        // Fetch all data
        $dataStmt = $localDb->query("SELECT * FROM `{$table}`");
        $rows = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            echo "✓\n";
            continue;
        }

        // Get columns
        $columns = array_keys($rows[0]);
        $columnList = '`' . implode('`, `', $columns) . '`';
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));

        // Prepare insert
        $insertSql = "INSERT INTO `{$table}` ({$columnList}) VALUES ({$placeholders})";
        $insertStmt = $railwayDb->prepare($insertSql);

        // Insert in batches
        $inserted = 0;
        foreach (array_chunk($rows, 100) as $batch) {
            $railwayDb->beginTransaction();
            foreach ($batch as $row) {
                try {
                    $insertStmt->execute(array_values($row));
                    $inserted++;
                }
                catch (PDOException $e) {
                    // Skip duplicate entries
                    if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                        echo "\n    Error: " . substr($e->getMessage(), 0, 80) . "...\n";
                    }
                }
            }
            $railwayDb->commit();
        }

        echo "✓ ({$inserted} rows)\n";
        $totalRows += $inserted;
    }

    $railwayDb->exec("SET FOREIGN_KEY_CHECKS=1");

    echo "\n=== Setup Complete! ===\n";
    echo "Total rows transferred: {$totalRows}\n\n";

    // Verify critical tables
    echo "Verifying critical tables...\n";
    $criticalTables = ['users', 'profiles', 'salons', 'platform_admins'];

    foreach ($criticalTables as $table) {
        $stmt = $railwayDb->query("SELECT COUNT(*) FROM `{$table}`");
        $count = $stmt->fetchColumn();
        echo "  {$table}: {$count} rows\n";
    }

    echo "\n✓ Railway database is ready!\n";
    echo "\nNext step: Test login at http://localhost:5174/login\n";


}
catch (PDOException $e) {
    echo "\n✗ Database Error: " . $e->getMessage() . "\n";
    exit(1);
}
catch (Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
