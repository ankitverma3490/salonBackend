<?php
/**
 * Create New Super Admin User in Railway Database
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

echo "<h1>New Super Admin Creation</h1>";
echo "<pre>";

try {
    $db = Database::getInstance();

    // Generate unique Super Admin credentials
    $email = 'superadmin@salon.com';
    $password = 'SuperAdmin@2024'; // Strong password
    $name = 'Super Administrator';
    $phone = '+1234567890';

    echo "Creating NEW Super Admin user...\n\n";

    // Check if user already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $existingUser = $stmt->fetch();

    if ($existingUser) {
        echo "⚠ User with email $email already exists. Updating...\n";
        $userId = $existingUser['id'];

        // Update password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET password_hash = ?, name = ?, phone = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $name, $phone, $userId]);
        echo "✓ User updated with ID: $userId\n";
    }
    else {
        // Create new user
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
        // Create profile with admin user_type
        $stmt = $db->prepare("
            INSERT INTO profiles (user_id, full_name, user_type, created_at, updated_at) 
            VALUES (?, ?, 'admin', NOW(), NOW())
        ");
        $stmt->execute([$userId, $name]);
        echo "✓ Profile created with user_type='admin'\n";
    }
    else {
        // Update existing profile to admin
        $stmt = $db->prepare("UPDATE profiles SET user_type = 'admin', full_name = ? WHERE user_id = ?");
        $stmt->execute([$name, $userId]);
        echo "✓ Profile updated to admin\n";
    }

    // Ensure platform_admins table exists with is_active column
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
        // Check if is_active column exists
        try {
            $db->query("SELECT is_active FROM platform_admins LIMIT 1");
        }
        catch (Exception $e) {
            echo "\nAdding is_active column...\n";
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
            INSERT INTO platform_admins (user_id, role, permissions, is_active, created_at, updated_at) 
            VALUES (?, 'super_admin', 'all', 1, NOW(), NOW())
        ");
        $stmt->execute([$userId]);
        echo "✓ Super Admin entry created in platform_admins\n";
    }
    else {
        // Update to ensure is_active = 1
        $stmt = $db->prepare("UPDATE platform_admins SET is_active = 1, role = 'super_admin', permissions = 'all' WHERE user_id = ?");
        $stmt->execute([$userId]);
        echo "✓ Super Admin entry updated\n";
    }

    echo "\n" . str_repeat("=", 60) . "\n";
    echo "<span style='color: green; font-weight: bold; font-size: 18px;'>✓ SUCCESS!</span>\n";
    echo str_repeat("=", 60) . "\n\n";

    echo "<strong>SUPER ADMIN CREDENTIALS:</strong>\n";
    echo str_repeat("-", 60) . "\n";
    echo "User ID:  <span style='color: blue; font-weight: bold;'>$userId</span>\n";
    echo "Email:    <span style='color: blue; font-weight: bold;'>$email</span>\n";
    echo "Password: <span style='color: blue; font-weight: bold;'>$password</span>\n";
    echo str_repeat("-", 60) . "\n\n";

    echo "Login URL: <a href='http://localhost:5173/login' target='_blank'>http://localhost:5173/login</a>\n\n";
    echo "<span style='color: orange;'>⚠ IMPORTANT: Save these credentials securely!</span>\n";
    echo "<span style='color: orange;'>⚠ Change the password after first login!</span>\n";


}
catch (Exception $e) {
    echo "\n<span style='color: red; font-weight: bold;'>✗ ERROR</span>\n\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    echo "Stack Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>";
