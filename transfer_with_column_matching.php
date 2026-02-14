<?php
/**
 * Transfer Data with Column Matching
 * Only transfers columns that exist in both databases
 */

error_reporting(E_ALL);
ini_set("display_errors", 1);
ini_set('max_execution_time', 600);

require_once __DIR__ . "/config.php";

$localHost = 'localhost';
$localPort = '3306';
$localDbName = 'salon_booking';
$localUser = 'root';
$localPass = '';

echo "=== Transferring Data with Column Matching ===\n\n";

try {
    $localDsn = "mysql:host={$localHost};port={$localPort};dbname={$localDbName};charset=utf8mb4";
    $localDb = new PDO($localDsn, $localUser, $localPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $railwayDsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $railwayDb = new PDO($railwayDsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    echo "✓ Connected to both databases\n\n";

    $railwayDb->exec("SET FOREIGN_KEY_CHECKS=0");

    $tableOrder = ['users', 'profiles', 'platform_admins', 'salons', 'user_roles', 'services', 'bookings'];
    $totalRows = 0;

    foreach ($tableOrder as $table) {
        echo "Processing {$table}...\n";

        // Get columns from Railway table
        try {
            $stmt = $railwayDb->query("SHOW COLUMNS FROM `{$table}`");
            $railwayColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
        catch (PDOException $e) {
            echo "  ✗ Table doesn't exist in Railway\n\n";
            continue;
        }

        // Get data from local
        try {
            $stmt = $localDb->query("SELECT * FROM `{$table}`");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch (PDOException $e) {
            echo "  ✗ Table doesn't exist in local\n\n";
            continue;
        }

        if (empty($rows)) {
            echo "  Empty table\n\n";
            continue;
        }

        // Find matching columns
        $localColumns = array_keys($rows[0]);
        $matchingColumns = array_intersect($localColumns, $railwayColumns);

        echo "  Local columns: " . count($localColumns) . "\n";
        echo "  Railway columns: " . count($railwayColumns) . "\n";
        echo "  Matching columns: " . count($matchingColumns) . "\n";

        if (empty($matchingColumns)) {
            echo "  ✗ No matching columns!\n\n";
            continue;
        }

        // Clear Railway table
        try {
            $railwayDb->exec("DELETE FROM `{$table}`");
        }
        catch (PDOException $e) {
            echo "  Warning: Could not clear table\n";
        }

        // Prepare insert
        $columnList = '`' . implode('`, `', $matchingColumns) . '`';
        $placeholders = implode(', ', array_fill(0, count($matchingColumns), '?'));
        $insertSql = "INSERT INTO `{$table}` ({$columnList}) VALUES ({$placeholders})";
        $insertStmt = $railwayDb->prepare($insertSql);

        // Insert rows
        $inserted = 0;
        $errors = 0;

        foreach ($rows as $row) {
            // Extract only matching columns
            $values = [];
            foreach ($matchingColumns as $col) {
                $values[] = $row[$col];
            }

            try {
                $insertStmt->execute($values);
                $inserted++;
            }
            catch (PDOException $e) {
                $errors++;
                if ($errors <= 3) {
                    echo "  Error: " . substr($e->getMessage(), 0, 80) . "...\n";
                }
            }
        }

        echo "  ✓ Inserted {$inserted}/" . count($rows) . " rows";
        if ($errors > 0) {
            echo " ({$errors} errors)";
        }
        echo "\n\n";

        $totalRows += $inserted;
    }

    $railwayDb->exec("SET FOREIGN_KEY_CHECKS=1");

    echo "=== Transfer Complete! ===\n";
    echo "Total rows: {$totalRows}\n\n";

    // Verify
    echo "Verification:\n";
    $tables = ['users', 'profiles', 'platform_admins', 'salons', 'user_roles', 'services', 'bookings'];

    foreach ($tables as $table) {
        try {
            $stmt = $railwayDb->query("SELECT COUNT(*) FROM `{$table}`");
            $count = $stmt->fetchColumn();
            echo "  {$table}: {$count} rows\n";
        }
        catch (PDOException $e) {
            echo "  {$table}: error\n";
        }
    }

    echo "\n✓ Data transfer complete!\n";
    echo "\nNext: Test login at http://localhost:5174/login\n";


}
catch (Exception $e) {
    echo "\nError: " . $e->getMessage() . "\n";
    exit(1);
}
