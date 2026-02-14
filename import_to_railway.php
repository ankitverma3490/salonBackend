<?php
/**
 * Import Database to Railway
 * This script imports the exported SQL file to Railway database
 */

error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once __DIR__ . "/config.php";

$importFile = __DIR__ . '/local_database_export.sql';

echo "=== Railway Database Import Tool ===\n\n";

// Check if export file exists
if (!file_exists($importFile)) {
    echo "✗ Error: Export file not found: {$importFile}\n";
    echo "Please run export_local_database.php first to create the export file.\n";
    exit(1);
}

echo "Import file: {$importFile}\n";
echo "File size: " . number_format(filesize($importFile) / 1024, 2) . " KB\n\n";

echo "Railway Database Configuration:\n";
echo "  Host: " . DB_HOST . "\n";
echo "  Port: " . DB_PORT . "\n";
echo "  Database: " . DB_NAME . "\n";
echo "  User: " . DB_USER . "\n\n";

// Confirm before proceeding
echo "⚠️  WARNING: This will overwrite existing data in the Railway database!\n";
echo "Press Enter to continue or Ctrl+C to cancel...\n";
if (php_sapi_name() === 'cli') {
    fgets(STDIN);
}

try {
    // Connect to Railway database
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    echo "Connecting to Railway database...\n";

    $db = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
    ]);

    echo "✓ Connected successfully!\n\n";

    // Read SQL file
    echo "Reading SQL file...\n";
    $sql = file_get_contents($importFile);

    // Split into individual statements
    // This is a simple splitter - semicolons in strings might cause issues
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    echo "Found " . count($statements) . " SQL statements\n\n";

    $executed = 0;
    $skipped = 0;
    $errors = 0;

    echo "Executing SQL statements...\n";

    foreach ($statements as $index => $statement) {
        if (empty($statement) || substr($statement, 0, 2) === '--') {
            continue;
        }

        // Show progress every 100 statements
        if ($index % 100 === 0 && $index > 0) {
            echo "  Progress: {$index}/" . count($statements) . " statements...\n";
        }

        try {
            $db->exec($statement);
            $executed++;
        }
        catch (PDOException $e) {
            // Skip "already exists" errors
            if (strpos($e->getMessage(), "already exists") !== false ||
            strpos($e->getMessage(), "Duplicate column") !== false ||
            strpos($e->getMessage(), "Duplicate entry") !== false) {
                $skipped++;
            }
            else {
                $errors++;
                echo "  ✗ Error in statement " . ($index + 1) . ": " . $e->getMessage() . "\n";
                echo "    Statement preview: " . substr($statement, 0, 100) . "...\n";

                // Stop on too many errors
                if ($errors > 10) {
                    echo "\n✗ Too many errors, stopping import.\n";
                    exit(1);
                }
            }
        }
    }

    echo "\n=== Import Complete! ===\n";
    echo "Statements executed: {$executed}\n";
    echo "Statements skipped: {$skipped}\n";
    echo "Errors encountered: {$errors}\n\n";

    // Verify tables
    echo "Verifying imported tables...\n";
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "✓ Found " . count($tables) . " tables in Railway database:\n";

    $totalRows = 0;
    foreach ($tables as $table) {
        $countStmt = $db->query("SELECT COUNT(*) FROM `{$table}`");
        $rowCount = $countStmt->fetchColumn();
        $totalRows += $rowCount;
        echo "  - {$table}: {$rowCount} rows\n";
    }

    echo "\nTotal rows imported: {$totalRows}\n\n";

    if ($errors === 0) {
        echo "✓ Import completed successfully!\n";
        echo "\nNext steps:\n";
        echo "1. Run verify_users.php to check user roles\n";
        echo "2. Test login from frontend at http://localhost:5174/login\n";
    }
    else {
        echo "⚠️  Import completed with {$errors} errors. Please review the errors above.\n";
    }


}
catch (PDOException $e) {
    echo "\n✗ Database Error: " . $e->getMessage() . "\n\n";
    echo "Please check your Railway database credentials in .env file\n";
    exit(1);
}
catch (Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
