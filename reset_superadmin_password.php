<?php
/**
 * Reset Superadmin Password
 * Sets password to "admin123" for testing
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

$db = Database::getInstance()->getConnection();

$email = 'superadmin@salon.com';
$newPassword = 'admin123';
$passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);

echo "Resetting password for: {$email}\n";
echo "New password: {$newPassword}\n\n";

$stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
$stmt->execute([$passwordHash, $email]);

if ($stmt->rowCount() > 0) {
    echo "✅ Password updated successfully!\n";
    echo "\nYou can now login with:\n";
    echo "  Email: {$email}\n";
    echo "  Password: {$newPassword}\n";
}
else {
    echo "❌ User not found or password not changed\n";
}
