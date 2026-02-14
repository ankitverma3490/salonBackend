<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT u.email, p.full_name, r.role FROM users u JOIN profiles p ON u.id = p.user_id LEFT JOIN user_roles r ON u.id = r.user_id WHERE u.email LIKE '%ankit%' OR p.full_name LIKE '%ankit%'");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($users, JSON_PRETTY_PRINT);
}
catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
