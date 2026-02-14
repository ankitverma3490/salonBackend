<?php
/**
 * Create Railway Schema - Remove ALL UUID defaults
 */

error_reporting(E_ALL);
ini_set("display_errors", 1);
ini_set('max_execution_time', 300);

require_once __DIR__ . "/config.php";

try {
    $railwayDsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    echo "Connecting to Railway database...\n";
    $db = new PDO($railwayDsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "✓ Connected\n\n";

    // Read schema
    $schemaFile = __DIR__ . '/database.sql';
    $sql = file_get_contents($schemaFile);

    // Remove ALL variations of DEFAULT UUID
    $sql = preg_replace('/DEFAULT\s*\(UUID\(\)\)/i', '', $sql);
    $sql = preg_replace('/DEFAULT\s+uuid\(\)/i', '', $sql);
    $sql = preg_replace('/DEFAULT\s+\(uuid\(\)\)/i', '', $sql);

    // Also remove DEFAULT (DATE_ADD...) which Railway might not support
    $sql = preg_replace('/DEFAULT\s*\(DATE_ADD[^)]+\)\)/i', 'DEFAULT NULL', $sql);

    // Save cleaned SQL for debugging
    file_put_contents(__DIR__ . '/database_railway_clean.sql', $sql);
    echo "✓ Created cleaned SQL file: database_railway_clean.sql\n\n";

    $db->exec("SET FOREIGN_KEY_CHECKS=0");
    $db->exec("SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO'");

    // Execute the entire SQL file
    echo "Executing schema...\n";
    try {
        $db->exec($sql);
        echo "✓ Schema executed successfully\n\n";
    }
    catch (PDOException $e) {
        echo "Error executing full schema: " . $e->getMessage() . "\n";
        echo "Trying statement-by-statement...\n\n";

        // Try statement by statement
        $statements = explode(';', $sql);
        $created = 0;
        $errors = 0;

        foreach ($statements as $statement) {
            $statement = trim($statement);

            if (empty($statement) || substr($statement, 0, 2) === '--') {
                continue;
            }

            // Extract table name if CREATE TABLE
            if (stripos($statement, 'CREATE TABLE') !== false) {
                preg_match('/CREATE TABLE\s+`?(\w+)`?/i', $statement, $matches);
                $tableName = $matches[1] ?? 'unknown';
                echo "Creating: {$tableName}... ";

                try {
                    $db->exec($statement);
                    echo "✓\n";
                    $created++;
                }
                catch (PDOException $e) {
                    echo "✗ " . substr($e->getMessage(), 0, 80) . "\n";
                    $errors++;
                }
            }
            else {
                // Execute other statements silently
                try {
                    $db->exec($statement);
                }
                catch (PDOException $e) {
                // Ignore errors for non-CREATE statements
                }
            }
        }

        echo "\nTables created: {$created}\n";
        echo "Errors: {$errors}\n\n";
    }

    $db->exec("SET FOREIGN_KEY_CHECKS=1");

    // Check what was created
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "=== Railway Database Status ===\n";
    echo "Total tables: " . count($tables) . "\n\n";

    if (count($tables) > 0) {
        echo "Tables created:\n";
        foreach ($tables as $table) {
            echo "  ✓ {$table}\n";
        }
    }
    else {
        echo "❌ NO TABLES CREATED\n";
    }


}
catch (Exception $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
    exit(1);
}
