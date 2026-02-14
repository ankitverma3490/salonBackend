<?php
/**
 * Create new salon owner account
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Auth.php';

$email = 'amansalon@gmail.com';
$password = 'admin123';
$fullName = 'Aman Salon Owner';

try {
    $db = Database::getInstance()->getConnection();

    // Check if user already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo "❌ User already exists: {$email}\n";
        exit(1);
    }

    $db->beginTransaction();

    // Create user
    $userId = Auth::generateUuid();
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $db->prepare("
        INSERT INTO users (id, email, password_hash, email_verified, created_at)
        VALUES (?, ?, ?, 1, NOW())
    ");
    $stmt->execute([$userId, $email, $hashedPassword]);

    echo "✅ Created user account\n";

    // Create profile
    $profileId = Auth::generateUuid();
    $stmt = $db->prepare("
        INSERT INTO profiles (id, user_id, full_name, user_type, created_at)
        VALUES (?, ?, ?, 'salon_owner', NOW())
    ");
    $stmt->execute([$profileId, $userId, $fullName]);

    echo "✅ Created profile\n";

    $db->commit();

    echo "\n=== Account Created Successfully ===\n\n";
    echo "Login credentials:\n";
    echo "  Email: {$email}\n";
    echo "  Password: {$password}\n";
    echo "  User Type: Salon Owner\n";
    echo "  User ID: {$userId}\n\n";
    echo "You can now login and create a salon!\n";


}
catch (Exception $e) {
    $db->rollBack();
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
