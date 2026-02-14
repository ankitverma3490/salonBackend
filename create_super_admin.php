<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Auth.php';

$db = Database::getInstance()->getConnection();

$email = 'superadmin@local.host';
$password = 'Admin@123456';
$fullName = 'Platform Overseer';

echo "Attempting to create Super Admin credentials...\n";

try {
    // 1. Check if user exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $existingUser = $stmt->fetch();

    if ($existingUser) {
        $userId = $existingUser['id'];
        echo "User already exists. ID: $userId\n";
    } else {
        // Create user
        // Using bin2hex(random_bytes(16)) for a clean 32-char ID
        $userId = bin2hex(random_bytes(16));
        $passwordHash = Auth::hashPassword($password);

        $stmt = $db->prepare("INSERT INTO users (id, email, password_hash) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $email, $passwordHash]);
        echo "Created User record.\n";

        // Create profile
        $stmt = $db->prepare("INSERT INTO profiles (user_id, full_name, user_type) VALUES (?, ?, 'admin')");
        $stmt->execute([$userId, $fullName]);
        echo "Created Profile record.\n";
    }

    // 2. Ensure entry in platform_admins
    $stmt = $db->prepare("SELECT id FROM platform_admins WHERE user_id = ?");
    $stmt->execute([$userId]);
    if (!$stmt->fetch()) {
        $adminId = bin2hex(random_bytes(16));
        $stmt = $db->prepare("INSERT INTO platform_admins (id, user_id, is_active) VALUES (?, ?, 1)");
        $stmt->execute([$adminId, $userId]);
        echo "Created Platform Admin record.\n";
    } else {
        echo "User is already in platform_admins.\n";
    }

    // 3. Update profile to 'admin' just in case
    $stmt = $db->prepare("UPDATE profiles SET user_type = 'admin' WHERE user_id = ?");
    $stmt->execute([$userId]);

    echo "\n-----------------------------------\n";
    echo "SUCCESS: Super Admin Created!\n";
    echo "Login Email: $email\n";
    echo "Login Pass:  $password\n";
    echo "-----------------------------------\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>