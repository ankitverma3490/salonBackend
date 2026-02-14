<?php
/**
 * Debug Data Transfer - Check what's failing
 */

error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once __DIR__ . "/config.php";

$localHost = 'localhost';
$localPort = '3306';
$localDbName = 'salon_booking';
$localUser = 'root';
$localPass = '';

try {
    $localDsn = "mysql:host={$localHost};port={$localPort};dbname={$localDbName};charset=utf8mb4";
    $localDb = new PDO($localDsn, $localUser, $localPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $railwayDsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $railwayDb = new PDO($railwayDsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    echo "Testing data transfer for users table...\n\n";

    // Get one user from local
    $stmt = $localDb->query("SELECT * FROM users LIMIT 1");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "No users in local database!\n";
        exit(1);
    }

    echo "Sample user from local:\n";
    print_r($user);
    echo "\n";

    // Try to insert into Railway
    $railwayDb->exec("SET FOREIGN_KEY_CHECKS=0");

    $columns = array_keys($user);
    $columnList = '`' . implode('`, `', $columns) . '`';
    $placeholders = implode(', ', array_fill(0, count($columns), '?'));
    $insertSql = "INSERT INTO users ({$columnList}) VALUES ({$placeholders})";

    echo "Insert SQL: {$insertSql}\n\n";
    echo "Values: " . implode(', ', array_values($user)) . "\n\n";

    try {
        $stmt = $railwayDb->prepare($insertSql);
        $stmt->execute(array_values($user));
        echo "✓ Successfully inserted user!\n";
    }
    catch (PDOException $e) {
        echo "✗ Error inserting user:\n";
        echo $e->getMessage() . "\n";
    }

    $railwayDb->exec("SET FOREIGN_KEY_CHECKS=1");


}
catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
