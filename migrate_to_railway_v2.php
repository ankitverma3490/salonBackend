<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/Database.php";

function executeSqlFile($db, $filePath) {
    if (!file_exists($filePath)) {
        echo "File NOT found: $filePath\n";
        return;
    }
    echo "Executing $filePath...\n";
    $sql = file_get_contents($filePath);
    
    // Simple splitter. Semicolons inside quotes or comments might break this.
    // But for these files it should be okay.
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($queries as $query) {
        if (empty($query)) continue;
        try {
            // Using PDO::exec helps avoid buffered query issues 
            // as it doesn't return a result set.
            $db->exec($query);
        } catch (Exception $e) {
            // Ignore "Already exists" errors during retry
            if (strpos($e->getMessage(), "already exists") !== false || 
                strpos($e->getMessage(), "Duplicate column name") !== false ||
                strpos($e->getMessage(), "Duplicate entry") !== false) {
                echo "  (Skipped: Already exists)\n";
            } else {
                echo "Error in $filePath: " . $e->getMessage() . "\n";
                echo "Query Start: " . substr($query, 0, 100) . "...\n";
            }
        }
    }
    echo "Finished $filePath.\n";
}

try {
    // Force buffered query attribute
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $db = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
    ]);
    
    echo "Connected to Database with Buffered Query enabled.\n";

    // 1. Master schema
    executeSqlFile($db, __DIR__ . "/database.sql");

    // 2. Incremental updates
    $files = glob(__DIR__ . "/*.sql");
    foreach ($files as $file) {
        if (basename($file) === "database.sql") continue;
        executeSqlFile($db, $file);
    }

    // 3. Migrations
    $migrations = glob(__DIR__ . "/migrations/*.sql");
    foreach ($migrations as $file) {
        executeSqlFile($db, $file);
    }

    echo "\nMigration (v2) Complete!\n";

} catch (Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
}
