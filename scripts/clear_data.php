<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Database.php';

try {
    $db = Database::getInstance()->getConnection();

    echo "--- Cleaning up Reviews Data ---\n";
    $db->exec("TRUNCATE TABLE booking_reviews");
    echo "Reviews table truncated.\n";

    echo "--- Cleaning up Users Data (excluding admins) ---\n";

    // Get admin user IDs to preserve them
    $stmt = $db->query("SELECT user_id FROM platform_admins");
    $adminIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Also include the email admin@salon.com if it's not in the list
    $stmt = $db->query("SELECT id FROM users WHERE email = 'admin@salon.com'");
    $mainAdminId = $stmt->fetchColumn();
    if ($mainAdminId && !in_array($mainAdminId, $adminIds)) {
        $adminIds[] = $mainAdminId;
    }

    if (!empty($adminIds)) {
        $placeholders = implode(',', array_fill(0, count($adminIds), '?'));

        // Remove from user_roles for non-admins
        $stmt = $db->prepare("DELETE FROM user_roles WHERE user_id NOT IN ($placeholders)");
        $stmt->execute($adminIds);
        echo "Deleted " . $stmt->rowCount() . " user roles.\n";

        // Remove from profiles for non-admins
        $stmt = $db->prepare("DELETE FROM profiles WHERE user_id NOT IN ($placeholders)");
        $stmt->execute($adminIds);
        echo "Deleted " . $stmt->rowCount() . " profiles.\n";

        // Remove from users for non-admins
        $stmt = $db->prepare("DELETE FROM users WHERE id NOT IN ($placeholders)");
        $stmt->execute($adminIds);
        echo "Deleted " . $stmt->rowCount() . " users.\n";
    } else {
        echo "No admin users found. Skipping user deletion for safety.\n";
    }

    echo "--- Cleanup Complete ---\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
