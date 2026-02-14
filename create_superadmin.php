<?php
/**
 * Create Super Admin User in Railway Database
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

echo "<h1>Super Admin Creation</h1>";
echo "<pre>";

try {
    $db = Database::getInstance();

    // Super Admin credentials
    $email = 'superadmin@salon.com';
    $password = 'SuperAdmin@2024'; // Strong password
    $name = 'Super Administrator';
    $phone = '+1234567890';

    echo "Creating Super Admin user...\n\n";

    // Check if user already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $existingUser = $stmt->fetch();

    if ($existingUser) {
        $userId = $existingUser['id'];
        echo "✓ User already exists with ID: $userId\n";

        // Update password in case it changed
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $userId]);
        echo "✓ Password updated\n";
    }
    else {
        // Create user
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("
            INSERT INTO users (email, password_hash, name, phone, created_at, updated_at) 
            VALUES (?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([$email, $hashedPassword, $name, $phone]);
        $userId = $db->lastInsertId();
        echo "✓ User created with ID: $userId\n";
    }

    // Check if profile exists
    $stmt = $db->prepare("SELECT id FROM profiles WHERE user_id = ?");
    $stmt->execute([$userId]);
    $existingProfile = $stmt->fetch();

    if (!$existingProfile) {
        // Create profile
        $stmt = $db->prepare("
            INSERT INTO profiles (user_id, user_type, created_at, updated_at) 
            VALUES (?, 'admin', NOW(), NOW())
        ");
        $stmt->execute([$userId]);
        echo "✓ Profile created\n";
    }
    else {
        echo "✓ Profile already exists\n";
    }

    // Check if platform_admins table exists
    try {
        $db->query("SELECT 1 FROM platform_admins LIMIT 1");
        $tableExists = true;
    }
    catch (Exception $e) {
        $tableExists = false;
    }

    if (!$tableExists) {
        echo "\nCreating platform_admins table...\n";
        $db->exec("
            CREATE TABLE IF NOT EXISTS platform_admins (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL UNIQUE,
                role VARCHAR(50) DEFAULT 'super_admin',
                permissions TEXT,
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "✓ platform_admins table created\n";
    }
    else {
        // Check if is_active column exists, add if missing
        try {
            $db->query("SELECT is_active FROM platform_admins LIMIT 1");
        }
        catch (Exception $e) {
            echo "\nAdding is_active column to platform_admins...\n";
            $db->exec("ALTER TABLE platform_admins ADD COLUMN is_active TINYINT(1) DEFAULT 1");
            echo "✓ is_active column added\n";
        }
    }

    // Check if admin entry exists
    $stmt = $db->prepare("SELECT id FROM platform_admins WHERE user_id = ?");
    $stmt->execute([$userId]);
    $existingAdmin = $stmt->fetch();

    if (!$existingAdmin) {
        // Create platform admin entry
        $stmt = $db->prepare("
            INSERT INTO platform_admins (user_id, role, permissions, created_at, updated_at) 
            VALUES (?, 'super_admin', 'all', NOW(), NOW())
        ");
        $stmt->execute([$userId]);
        echo "✓ Super Admin entry created\n";
    }
    else {
        echo "✓ Super Admin entry already exists\n";
    }

    echo "\n<span style='color: green; font-weight: bold;'>✓ SUCCESS!</span>\n\n";
    echo "Super Admin Credentials:\n";
    echo "Email: $email\n";
    echo "Password: $password\n";
    echo "\nYou can now login at: http://localhost:5173/login\n";
    echo "\n<strong>IMPORTANT: Change the password after first login!</strong>\n";


}
catch (Exception $e) {
    echo "\n<span style='color: red; font-weight: bold;'>✗ ERROR</span>\n\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "</pre>";
