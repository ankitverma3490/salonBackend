<?php
/**
 * Reset password for specific user
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

$email = 'amansalon@gmail.com';
$newPassword = 'admin123';

try {
    $db = Database::getInstance()->getConnection();

    // Check if user exists
    $stmt = $db->prepare("SELECT id, email FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "❌ User not found: {$email}\n";
        exit(1);
    }

    echo "Found user: {$user['email']}\n";
    echo "User ID: {$user['id']}\n\n";

    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

    // Update password
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashedPassword, $user['id']]);

    echo "✅ Password reset successfully!\n\n";
    echo "Login credentials:\n";
    echo "  Email: {$email}\n";
    echo "  Password: {$newPassword}\n";


}
catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
