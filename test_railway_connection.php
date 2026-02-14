<?php
/**
 * Railway Database Connection Test
 * Tests all role-based API endpoints
 */

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

$results = [
    'timestamp' => date('Y-m-d H:i:s'),
    'database' => [
        'host' => DB_HOST,
        'port' => DB_PORT,
        'name' => DB_NAME,
        'status' => 'disconnected'
    ],
    'tables' => [],
    'roles' => []
];

try {
    // Test database connection
    $db = Database::getInstance();
    $results['database']['status'] = 'connected';

    // Test critical tables
    $tables = [
        'users' => 'SELECT COUNT(*) as count FROM users',
        'profiles' => 'SELECT COUNT(*) as count FROM profiles',
        'platform_admins' => 'SELECT COUNT(*) as count FROM platform_admins',
        'salons' => 'SELECT COUNT(*) as count FROM salons',
        'user_roles' => 'SELECT COUNT(*) as count FROM user_roles',
        'bookings' => 'SELECT COUNT(*) as count FROM bookings',
        'services' => 'SELECT COUNT(*) as count FROM services',
        'notifications' => 'SELECT COUNT(*) as count FROM notifications'
    ];

    foreach ($tables as $table => $query) {
        try {
            $result = $db->query($query)->fetch();
            $results['tables'][$table] = [
                'status' => 'ok',
                'count' => $result['count']
            ];
        }
        catch (Exception $e) {
            $results['tables'][$table] = [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Test role-based data
    try {
        // Super Admin
        $adminCount = $db->query("SELECT COUNT(*) as count FROM platform_admins")->fetch();
        $results['roles']['super_admin'] = [
            'status' => 'ok',
            'count' => $adminCount['count']
        ];

        // Salon Owners
        $ownerCount = $db->query("SELECT COUNT(*) as count FROM user_roles WHERE role = 'owner'")->fetch();
        $results['roles']['salon_owners'] = [
            'status' => 'ok',
            'count' => $ownerCount['count']
        ];

        // Staff
        $staffCount = $db->query("SELECT COUNT(*) as count FROM user_roles WHERE role = 'staff'")->fetch();
        $results['roles']['staff'] = [
            'status' => 'ok',
            'count' => $staffCount['count']
        ];

        // Customers
        $customerCount = $db->query("SELECT COUNT(*) as count FROM profiles WHERE user_type = 'customer'")->fetch();
        $results['roles']['customers'] = [
            'status' => 'ok',
            'count' => $customerCount['count']
        ];

    }
    catch (Exception $e) {
        $results['roles']['error'] = $e->getMessage();
    }

    $results['overall_status'] = 'success';
    $results['message'] = 'All systems connected to Railway database';


}
catch (Exception $e) {
    $results['overall_status'] = 'error';
    $results['message'] = $e->getMessage();
    $results['database']['error'] = $e->getMessage();
}

echo json_encode($results, JSON_PRETTY_PRINT);
