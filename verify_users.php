<?php
/**
 * Verify Users and Roles in Railway Database
 * This script checks user accounts, roles, and permissions
 */

error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/Database.php";

echo "=== User Role Verification Tool ===\n\n";

try {
    $db = Database::getInstance()->getConnection();

    echo "Connected to Railway database: " . DB_NAME . "\n\n";

    // Check total users
    echo "--- USER ACCOUNTS ---\n";
    $stmt = $db->query("SELECT COUNT(*) FROM users");
    $userCount = $stmt->fetchColumn();
    echo "Total users: {$userCount}\n\n";

    if ($userCount > 0) {
        // Show all users with their profiles
        $stmt = $db->query("
            SELECT 
                u.id,
                u.email,
                u.email_verified,
                p.full_name,
                p.user_type,
                u.created_at
            FROM users u
            LEFT JOIN profiles p ON u.id = p.user_id
            ORDER BY u.created_at DESC
            LIMIT 20
        ");

        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "Recent users:\n";
        foreach ($users as $user) {
            echo sprintf(
                "  [%s] %s - %s (%s) - Verified: %s\n",
                substr($user['id'], 0, 8),
                $user['email'],
                $user['full_name'] ?: 'No name',
                $user['user_type'] ?: 'No type',
                $user['email_verified'] ? 'Yes' : 'No'
            );
        }
        echo "\n";
    }

    // Check platform admins (superadmins)
    echo "--- PLATFORM ADMINS (SUPERADMIN) ---\n";
    $stmt = $db->query("
        SELECT 
            pa.id,
            u.email,
            p.full_name,
            pa.is_active,
            pa.created_at
        FROM platform_admins pa
        JOIN users u ON pa.user_id = u.id
        LEFT JOIN profiles p ON u.id = p.user_id
        ORDER BY pa.created_at DESC
    ");

    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($admins)) {
        echo "⚠️  WARNING: No superadmin accounts found!\n";
        echo "You need to create a superadmin account to access the admin panel.\n";
        echo "Run: php create_superadmin.php\n\n";
    }
    else {
        echo "Found " . count($admins) . " superadmin account(s):\n";
        foreach ($admins as $admin) {
            echo sprintf(
                "  [%s] %s - %s - Active: %s\n",
                substr($admin['id'], 0, 8),
                $admin['email'],
                $admin['full_name'] ?: 'No name',
                $admin['is_active'] ? 'Yes' : 'No'
            );
        }
        echo "\n";
    }

    // Check salon owners
    echo "--- SALON OWNERS ---\n";
    $stmt = $db->query("
        SELECT 
            s.id,
            s.name,
            u.email,
            p.full_name,
            s.approval_status,
            s.is_active
        FROM salons s
        LEFT JOIN user_roles ur ON s.id = ur.salon_id AND ur.role = 'owner'
        LEFT JOIN users u ON ur.user_id = u.id
        LEFT JOIN profiles p ON u.id = p.user_id
        ORDER BY s.created_at DESC
        LIMIT 10
    ");

    $salons = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($salons)) {
        echo "No salon accounts found.\n\n";
    }
    else {
        echo "Found " . count($salons) . " salon(s):\n";
        foreach ($salons as $salon) {
            echo sprintf(
                "  [%s] %s - Owner: %s (%s) - Status: %s - Active: %s\n",
                substr($salon['id'], 0, 8),
                $salon['name'],
                $salon['email'] ?: 'No owner',
                $salon['full_name'] ?: 'No name',
                $salon['approval_status'],
                $salon['is_active'] ? 'Yes' : 'No'
            );
        }
        echo "\n";
    }

    // Check user roles
    echo "--- USER ROLES ---\n";
    $stmt = $db->query("
        SELECT 
            role,
            COUNT(*) as count
        FROM user_roles
        GROUP BY role
    ");

    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($roles)) {
        echo "No user roles assigned.\n\n";
    }
    else {
        echo "Role distribution:\n";
        foreach ($roles as $role) {
            echo "  {$role['role']}: {$role['count']}\n";
        }
        echo "\n";
    }

    // Check profiles by user type
    echo "--- PROFILE TYPES ---\n";
    $stmt = $db->query("
        SELECT 
            user_type,
            COUNT(*) as count
        FROM profiles
        GROUP BY user_type
    ");

    $types = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($types)) {
        echo "No profiles found.\n\n";
    }
    else {
        echo "User type distribution:\n";
        foreach ($types as $type) {
            echo "  {$type['user_type']}: {$type['count']}\n";
        }
        echo "\n";
    }

    // Summary and recommendations
    echo "--- SUMMARY ---\n";

    $issues = [];

    if (empty($admins)) {
        $issues[] = "No superadmin accounts - create one with create_superadmin.php";
    }

    if ($userCount === 0) {
        $issues[] = "No users in database - import may have failed";
    }

    if (empty($issues)) {
        echo "✓ All checks passed! Database appears to be properly configured.\n\n";
        echo "You should now be able to:\n";
        echo "1. Login as superadmin at: http://localhost:5174/login\n";
        if (!empty($salons)) {
            echo "2. Login as salon owner at: http://localhost:5174/login\n";
        }
    }
    else {
        echo "⚠️  Issues found:\n";
        foreach ($issues as $issue) {
            echo "  - {$issue}\n";
        }
        echo "\n";
    }


}
catch (PDOException $e) {
    echo "✗ Database Error: " . $e->getMessage() . "\n";
    echo "Make sure the database is properly imported.\n";
    exit(1);
}
catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
