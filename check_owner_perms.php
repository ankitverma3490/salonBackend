<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT role, p.name FROM role_permissions rp JOIN permissions p ON rp.permission_id = p.id WHERE rp.role = 'owner'");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['role'] . " -> " . $row['name'] . "\n";
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
