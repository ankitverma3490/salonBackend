<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

$db = Database::getInstance()->getConnection();

echo "--- STAFF PROFILES ---\n";
$stmt = $db->query("SELECT * FROM staff_profiles");
$staff = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($staff);

echo "\n--- STAFF SERVICES ---\n";
$stmt = $db->query("SELECT * FROM staff_services");
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($services);

echo "\n--- USER ROLES ---\n";
$stmt = $db->query("SELECT * FROM user_roles");
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($roles);
