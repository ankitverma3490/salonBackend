<?php
// Test database connection and verify tables
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    // Connect to database
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $db = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $result = [
        'status' => 'success',
        'database' => DB_NAME,
        'tables' => []
    ];

    // Check if tables exist
    $tablesStmt = $db->query("SHOW TABLES");
    $dbTables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($dbTables as $table) {
        $countStmt = $db->query("SELECT COUNT(*) as count FROM `$table` ");
        $count = $countStmt->fetch()['count'];
        $result['tables'][$table] = [
            'exists' => true,
            'rows' => $count
        ];
    }

    $requiredTables = ['users', 'profiles', 'salons', 'user_roles', 'services', 'bookings', 'platform_admins', 'platform_payments', 'subscription_plans', 'salon_subscriptions', 'platform_settings'];
    foreach ($requiredTables as $table) {
        if (!in_array($table, $dbTables)) {
            $result['tables'][$table] = [
                'exists' => false,
                'rows' => 0
            ];
            $result['status'] = 'warning';
        }
    }

    // Test a simple query
    $stmt = $db->query("SELECT COUNT(*) as total FROM users");
    $userCount = $stmt->fetch()['total'];
    $result['total_users'] = $userCount;

    echo json_encode($result, JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed: ' . $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
