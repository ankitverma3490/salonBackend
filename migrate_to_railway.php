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
    
    // Split into individual queries using semicolon
    // This is a simple splitter; might fail on procedures or complex triggers
    // but for standard table definitions it works.
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($queries as $query) {
        if (empty($query)) continue;
        try {
            $db->exec($query);
        } catch (Exception $e) {
            echo "Error in $filePath: " . $e->getMessage() . "\n";
            echo "Query: " . substr($query, 0, 100) . "...\n";
        }
    }
    echo "Finished $filePath.\n";
}

try {
    $db = Database::getInstance()->getConnection();
    echo "Connected to Database.\n";

    // 1. Run the master schema
    executeSqlFile($db, __DIR__ . "/database.sql");

    // 2. Run incremental updates from backend root
    $files = glob(__DIR__ . "/*.sql");
    foreach ($files as $file) {
        if (basename($file) === "database.sql") continue;
        executeSqlFile($db, $file);
    }

    // 3. Run migrations folder
    $migrations = glob(__DIR__ . "/migrations/*.sql");
    foreach ($migrations as $file) {
        executeSqlFile($db, $file);
    }

    echo "\nMigration Complete!\n";

} catch (Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
}
