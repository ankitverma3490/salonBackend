<?php
/**
 * Export Local Database to SQL File
 * This script exports the complete local database (schema + data) to a SQL file
 * that can be imported into Railway database
 */

error_reporting(E_ALL);
ini_set("display_errors", 1);

// Local database configuration
// Update these values with your local database credentials
$localHost = 'localhost';
$localPort = '3306';
$localDbName = 'salon_booking'; // Update if different
$localUser = 'root'; // Update with your local MySQL username
$localPass = ''; // Update with your local MySQL password

$outputFile = __DIR__ . '/local_database_export.sql';

echo "=== Local Database Export Tool ===\n\n";

try {
    // Connect to local database
    $dsn = "mysql:host={$localHost};port={$localPort};dbname={$localDbName};charset=utf8mb4";
    echo "Connecting to local database: {$localDbName}...\n";

    $db = new PDO($dsn, $localUser, $localPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
    ]);

    echo "✓ Connected successfully!\n\n";

    // Open output file
    $fp = fopen($outputFile, 'w');
    if (!$fp) {
        throw new Exception("Cannot create output file: {$outputFile}");
    }

    // Write header
    fwrite($fp, "-- Database Export from Local MySQL\n");
    fwrite($fp, "-- Database: {$localDbName}\n");
    fwrite($fp, "-- Generated: " . date('Y-m-d H:i:s') . "\n");
    fwrite($fp, "-- \n\n");
    fwrite($fp, "SET FOREIGN_KEY_CHECKS=0;\n");
    fwrite($fp, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
    fwrite($fp, "SET time_zone = \"+00:00\";\n\n");

    // Get all tables
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "Found " . count($tables) . " tables to export:\n";
    foreach ($tables as $table) {
        echo "  - {$table}\n";
    }
    echo "\n";

    $exportedTables = 0;
    $totalRows = 0;

    // Export each table
    foreach ($tables as $table) {
        echo "Exporting table: {$table}... ";

        // Drop table statement
        fwrite($fp, "\n-- --------------------------------------------------------\n");
        fwrite($fp, "-- Table structure for table `{$table}`\n");
        fwrite($fp, "-- --------------------------------------------------------\n\n");
        fwrite($fp, "DROP TABLE IF EXISTS `{$table}`;\n\n");

        // Create table statement
        $createStmt = $db->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC);
        fwrite($fp, $createStmt['Create Table'] . ";\n\n");

        // Get row count
        $countStmt = $db->query("SELECT COUNT(*) FROM `{$table}`");
        $rowCount = $countStmt->fetchColumn();

        if ($rowCount > 0) {
            fwrite($fp, "-- Dumping data for table `{$table}`\n");
            fwrite($fp, "-- {$rowCount} rows\n\n");

            // Export data in batches
            $batchSize = 100;
            $offset = 0;

            while ($offset < $rowCount) {
                $dataStmt = $db->query("SELECT * FROM `{$table}` LIMIT {$batchSize} OFFSET {$offset}");
                $rows = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

                if (!empty($rows)) {
                    // Get column names from first row
                    $columns = array_keys($rows[0]);
                    $columnList = '`' . implode('`, `', $columns) . '`';

                    fwrite($fp, "INSERT INTO `{$table}` ({$columnList}) VALUES\n");

                    $values = [];
                    foreach ($rows as $row) {
                        $escapedValues = array_map(function ($value) use ($db) {
                            if ($value === null) {
                                return 'NULL';
                            }
                            return $db->quote($value);
                        }, array_values($row));
                        $values[] = '(' . implode(', ', $escapedValues) . ')';
                    }

                    fwrite($fp, implode(",\n", $values) . ";\n\n");
                }

                $offset += $batchSize;
            }

            $totalRows += $rowCount;
            echo "✓ ({$rowCount} rows)\n";
        }
        else {
            echo "✓ (empty)\n";
        }

        $exportedTables++;
    }

    // Write footer
    fwrite($fp, "\nSET FOREIGN_KEY_CHECKS=1;\n");
    fwrite($fp, "\n-- Export completed: " . date('Y-m-d H:i:s') . "\n");
    fwrite($fp, "-- Total tables: {$exportedTables}\n");
    fwrite($fp, "-- Total rows: {$totalRows}\n");

    fclose($fp);

    echo "\n=== Export Complete! ===\n";
    echo "Tables exported: {$exportedTables}\n";
    echo "Total rows: {$totalRows}\n";
    echo "Output file: {$outputFile}\n";
    echo "File size: " . number_format(filesize($outputFile) / 1024, 2) . " KB\n\n";
    echo "Next step: Run import_to_railway.php to import this data to Railway\n";


}
catch (PDOException $e) {
    echo "\n✗ Database Error: " . $e->getMessage() . "\n\n";
    echo "Please check your local database credentials:\n";
    echo "  Host: {$localHost}\n";
    echo "  Port: {$localPort}\n";
    echo "  Database: {$localDbName}\n";
    echo "  User: {$localUser}\n";
    echo "\nUpdate the credentials at the top of this file and try again.\n";
    exit(1);
}
catch (Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
