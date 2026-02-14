<?php
/**
 * List All User Credentials (for testing)
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

$db = Database::getInstance()->getConnection();

echo "=== All User Accounts ===\n\n";

$stmt = $db->query("
    SELECT u.email, p.full_name, p.user_type, pa.is_active as is_admin
    FROM users u
    LEFT JOIN profiles p ON u.id = p.user_id
    LEFT JOIN platform_admins pa ON u.id = pa.user_id
    ORDER BY p.user_type DESC, u.email
");

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $user) {
    $type = $user['user_type'] ?? 'unknown';
    $isAdmin = $user['is_admin'] ? ' [SUPERADMIN]' : '';
    echo "{$user['email']} - {$user['full_name']} ({$type}){$isAdmin}\n";
}

echo "\n=== Password Testing ===\n";
echo "Note: The actual passwords were set when users were created.\n";
echo "Common test passwords to try:\n";
echo "  - admin123\n";
echo "  - password\n";
echo "  - 123456\n";
echo "  - superadmin\n";
echo "\nTo reset a password, you can run:\n";
echo "UPDATE users SET password_hash = ? WHERE email = ?\n";
