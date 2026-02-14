<?php
/**
 * Delete all users and salons except superadmin
 * If no superadmin exists, preserve users with 'admin' role or email containing 'admin'
 */

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/Database.php";

try {
    $db = Database::getInstance()->getConnection();

    echo "=== Cleaning Database (Preserving Admin Accounts) ===\n\n";

    // Start transaction
    $db->beginTransaction();

    // 1. Get admin user IDs (super_admin or admin role, or admin email)
    echo "1. Identifying admin users... ";
    $stmt = $db->query("
        SELECT DISTINCT u.id, u.email, ur.role
        FROM users u
        LEFT JOIN user_roles ur ON u.id = ur.user_id
        WHERE ur.role IN ('super_admin', 'admin') 
           OR u.email LIKE '%admin%'
           OR u.email LIKE '%superadmin%'
        ORDER BY u.email
    ");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($admins)) {
        echo "No admin found! Checking all users...\n";
        $stmt = $db->query("SELECT id, email FROM users LIMIT 5");
        $allUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Sample users in database:\n";
        foreach ($allUsers as $user) {
            echo "  - {$user['email']} (ID: {$user['id']})\n";
        }
        echo "\nPlease specify which user to preserve, or create a superadmin first.\n";
        $db->rollBack();
        exit(1);
    }

    $adminIds = array_column($admins, 'id');
    echo "Found " . count($admins) . " admin(s) to preserve:\n";
    foreach ($admins as $admin) {
        $role = $admin['role'] ?? 'no role';
        echo "  - {$admin['email']} (Role: $role)\n";
    }

    // 2. Delete all salons (CASCADE will handle related data)
    echo "\n2. Deleting all salons... ";
    $stmt = $db->query("SELECT COUNT(*) FROM salons");
    $salonCount = $stmt->fetchColumn();

    $db->exec("DELETE FROM salons");
    echo "Deleted $salonCount salon(s).\n";

    // 3. Delete non-admin users (CASCADE will handle related data)
    echo "3. Deleting non-admin users... ";

    $placeholders = str_repeat('?,', count($adminIds) - 1) . '?';
    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE id NOT IN ($placeholders)");
    $stmt->execute($adminIds);
    $userCount = $stmt->fetchColumn();

    $stmt = $db->prepare("DELETE FROM users WHERE id NOT IN ($placeholders)");
    $stmt->execute($adminIds);
    echo "Deleted $userCount user(s).\n";

    // 4. Clean up orphaned data
    echo "4. Cleaning orphaned data... ";

    $cleanupTables = [
        'profiles' => "DELETE FROM profiles WHERE user_id NOT IN ($placeholders)",
        'user_roles' => "DELETE FROM user_roles WHERE user_id NOT IN ($placeholders) AND role NOT IN ('super_admin', 'admin')",
        'bookings' => "TRUNCATE TABLE bookings",
        'services' => "TRUNCATE TABLE services",
        'staff_profiles' => "TRUNCATE TABLE staff_profiles",
        'staff_attendance' => "TRUNCATE TABLE staff_attendance",
        'booking_reviews' => "TRUNCATE TABLE booking_reviews",
        'messages' => "DELETE FROM messages WHERE sender_id NOT IN ($placeholders) OR recipient_id NOT IN ($placeholders)"
    ];

    foreach ($cleanupTables as $table => $sql) {
        try {
            if (strpos($sql, 'TRUNCATE') !== false) {
                $db->exec($sql);
                echo "$table ";
            }
            else {
                $stmt = $db->prepare($sql);
                $stmt->execute($adminIds);
                $affected = $stmt->rowCount();
                if ($affected > 0) {
                    echo "$table($affected) ";
                }
            }
        }
        catch (PDOException $e) {
        // Table might not exist
        }
    }
    echo "Done.\n";

    // Commit transaction
    $db->commit();

    echo "\n✓ Database cleaned successfully!\n";
    echo "Preserved admin accounts:\n";
    foreach ($admins as $admin) {
        echo "  - {$admin['email']}\n";
    }

}
catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo "\nError: " . $e->getMessage() . "\n";
    exit(1);
}
