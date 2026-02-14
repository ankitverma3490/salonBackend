<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Auth.php';

header('Content-Type: application/json; charset=UTF-8');

$results = [
    'database_connection' => false,
    'tables_check' => [],
    'sample_data' => [],
    'errors' => []
];

try {
    // Test 1: Database Connection
    $db = Database::getInstance()->getConnection();
    $results['database_connection'] = true;
    $results['database_name'] = DB_NAME;

    // Test 2: Check Required Tables
    $requiredTables = [
        'users',
        'profiles',
        'salons',
        'services',
        'bookings',
        'user_roles',
        'staff_members'
    ];

    foreach ($requiredTables as $table) {
        try {
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM $table");
            $stmt->execute();
            $result = $stmt->fetch();
            $results['tables_check'][$table] = [
                'exists' => true,
                'count' => $result['count']
            ];
        } catch (Exception $e) {
            $results['tables_check'][$table] = [
                'exists' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    // Test 3: Check Users and Profiles
    $stmt = $db->prepare("
        SELECT u.id, u.email, p.full_name, p.user_type, p.phone
        FROM users u
        LEFT JOIN profiles p ON u.id = p.user_id
        LIMIT 5
    ");
    $stmt->execute();
    $results['sample_data']['users'] = $stmt->fetchAll();

    // Test 4: Check Salons
    $stmt = $db->prepare("
        SELECT id, name, slug, email, phone, approval_status, is_active
        FROM salons
        LIMIT 5
    ");
    $stmt->execute();
    $results['sample_data']['salons'] = $stmt->fetchAll();

    // Test 5: Check Services
    $stmt = $db->prepare("
        SELECT s.id, s.name, s.price, s.duration_minutes, s.category, sal.name as salon_name
        FROM services s
        LEFT JOIN salons sal ON s.salon_id = sal.id
        LIMIT 5
    ");
    $stmt->execute();
    $results['sample_data']['services'] = $stmt->fetchAll();

    // Test 6: Check User Roles
    $stmt = $db->prepare("
        SELECT ur.id, u.email, s.name as salon_name, ur.role
        FROM user_roles ur
        LEFT JOIN users u ON ur.user_id = u.id
        LEFT JOIN salons s ON ur.salon_id = s.id
        LIMIT 5
    ");
    $stmt->execute();
    $results['sample_data']['user_roles'] = $stmt->fetchAll();

    // Test 7: Check Salon Owners
    $stmt = $db->prepare("
        SELECT u.email, p.full_name, s.name as salon_name, ur.role
        FROM users u
        INNER JOIN profiles p ON u.id = p.user_id
        INNER JOIN user_roles ur ON u.id = ur.user_id
        INNER JOIN salons s ON ur.salon_id = s.id
        WHERE p.user_type = 'salon_owner' AND ur.role = 'owner'
    ");
    $stmt->execute();
    $results['sample_data']['salon_owners'] = $stmt->fetchAll();

    // Test 8: Check Database Structure
    $stmt = $db->prepare("SHOW TABLES");
    $stmt->execute();
    $allTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $results['all_tables'] = $allTables;

    $results['status'] = 'success';
    $results['message'] = 'All database tests completed successfully';

} catch (Exception $e) {
    $results['status'] = 'error';
    $results['errors'][] = $e->getMessage();
}

echo json_encode($results, JSON_PRETTY_PRINT);
