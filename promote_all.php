<?php
require_once __DIR__ . '/Database.php';

$db = Database::getInstance()->getConnection();

// Get all users
$stmt = $db->query("SELECT id, email FROM users");
$users = $stmt->fetchAll();

echo "Users in system:\n";
foreach ($users as $user) {
    echo "ID: " . $user['id'] . " | Email: " . $user['email'] . "\n";

    // Check if already admin
    $check = $db->prepare("SELECT id FROM platform_admins WHERE user_id = ?");
    $check->execute([$user['id']]);
    if (!$check->fetch()) {
        $ins = $db->prepare("INSERT INTO platform_admins (id, user_id, is_active) VALUES (REPLACE(UUID(), '-', ''), ?, 1)");
        $ins->execute([$user['id']]);
        echo "--> PROMOTED TO SUPER ADMIN TABLE\n";
    } else {
        echo "--> ALREADY IN ADMIN TABLE\n";
    }

    // Update profile type
    $upd = $db->prepare("UPDATE profiles SET user_type = 'admin' WHERE user_id = ?");
    $upd->execute([$user['id']]);
    echo "--> PROFILE TYPE ACTIVATED AS ADMIN\n";
}
?>