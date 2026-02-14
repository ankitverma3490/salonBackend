<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Database.php';

try {
    $db = Database::getInstance()->getConnection();

    echo "--- Approving all pending salons ---\n";
    $stmt = $db->query("UPDATE salons SET approval_status = 'approved' WHERE approval_status = 'pending'");
    echo "Updated " . $stmt->rowCount() . " salons.\n";

    echo "--- User Roles Detailed --- \n";
    $stmt = $db->query("
        SELECT u.email, p.user_type, ur.role as assigned_role, s.name as salon_name, s.approval_status
        FROM users u 
        LEFT JOIN profiles p ON u.id = p.user_id 
        LEFT JOIN user_roles ur ON u.id = ur.user_id
        LEFT JOIN salons s ON ur.salon_id = s.id
    ");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
