<?php
/**
 * Reset password for superadmin@salon.com
 */

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/Database.php";

try {
    $db = Database::getInstance()->getConnection();

    $email = "superadmin@salon.com";
    $newPassword = "admin123"; // Default password

    // Hash the password
    $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);

    // Update the password
    $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
    $stmt->execute([$passwordHash, $email]);

    if ($stmt->rowCount() > 0) {
        echo "✓ Password reset successful!\n\n";
        echo "Email: $email\n";
        echo "New Password: $newPassword\n";
    }
    else {
        echo "✗ User not found: $email\n";

        // Check if user exists
        $stmt = $db->prepare("SELECT email FROM users WHERE email LIKE '%superadmin%'");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($users)) {
            echo "\nFound similar users:\n";
            foreach ($users as $user) {
                echo "  - $user\n";
            }
        }
    }

}
catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
