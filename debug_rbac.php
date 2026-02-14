<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();

    echo "--- Permissions ---\n";
    $permissions = $db->query("SELECT * FROM permissions")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($permissions as $p) {
        echo "{$p['name']} ({$p['id']})\n";
    }

    echo "\n--- Role Permissions ---\n";
    $rp = $db->query("SELECT role, p.name FROM role_permissions rp JOIN permissions p ON rp.permission_id = p.id")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rp as $r) {
        echo "{$r['role']} -> {$r['name']}\n";
    }

    echo "\n--- Recent User Roles ---\n";
    $roles = $db->query("SELECT ur.*, u.email, s.name as salon_name FROM user_roles ur JOIN users u ON ur.user_id = u.id JOIN salons s ON ur.salon_id = s.id ORDER BY ur.created_at DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($roles as $r) {
        echo "{$r['email']} is {$r['role']} in {$r['salon_name']} (Salon ID: {$r['salon_id']})\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
