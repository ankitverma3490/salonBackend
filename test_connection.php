<?php
/**
 * Quick Railway Connection Diagnostic
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Railway Database Connection Test</h1>";
echo "<pre>";

// Load environment
if (file_exists(__DIR__ . '/../.env')) {
    echo "✓ .env file found\n\n";
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0)
            continue;
        $parts = explode('=', $line, 2);
        if (count($parts) !== 2)
            continue;
        $name = trim($parts[0]);
        $value = trim($parts[1]);
        putenv(sprintf('%s=%s', $name, $value));
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;

        // Show config (hide password)
        if ($name === 'DB_PASS') {
            echo "$name = " . str_repeat('*', strlen($value)) . "\n";
        }
        else if (strpos($name, 'DB_') === 0) {
            echo "$name = $value\n";
        }
    }
}
else {
    echo "✗ .env file NOT found at: " . __DIR__ . '/../.env' . "\n";
    die();
}

echo "\n--- Testing Connection ---\n";

$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '3306';
$dbname = getenv('DB_NAME') ?: 'railway';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';

echo "Host: $host\n";
echo "Port: $port\n";
echo "Database: $dbname\n";
echo "User: $user\n\n";

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4;connect_timeout=15";
    echo "DSN: $dsn\n\n";

    echo "Attempting connection...\n";
    $start = microtime(true);

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_TIMEOUT => 15,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $elapsed = round(microtime(true) - $start, 2);

    echo "✓ CONNECTION SUCCESSFUL! (took {$elapsed}s)\n\n";

    // Test query
    $result = $pdo->query("SELECT COUNT(*) as count FROM users")->fetch();
    echo "✓ Query test passed. Users count: " . $result['count'] . "\n";

    echo "\n<span style='color: green; font-weight: bold;'>✓ ALL TESTS PASSED</span>\n";


}
catch (PDOException $e) {
    $elapsed = round(microtime(true) - $start, 2);
    echo "\n<span style='color: red; font-weight: bold;'>✗ CONNECTION FAILED (after {$elapsed}s)</span>\n\n";
    echo "Error Code: " . $e->getCode() . "\n";
    echo "Error Message: " . $e->getMessage() . "\n\n";

    echo "Common fixes:\n";
    echo "1. Check if Railway database credentials are correct\n";
    echo "2. Verify Railway database is running\n";
    echo "3. Check if your IP is whitelisted (if Railway requires it)\n";
    echo "4. Ensure PHP PDO MySQL extension is installed\n";
}

echo "</pre>";
