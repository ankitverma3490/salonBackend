<?php
require_once __DIR__ . '/backend/config.php';
require_once __DIR__ . '/backend/Database.php';

try {
    $db = Database::getInstance()->getConnection();

    echo "=== SYSTEM DIAGNOSTIC ===\n\n";

    echo "--- SALON OWNERS ---\n";
    $owners = $db->query("
        SELECT u.id, u.email, p.user_type, ur.role, s.name as salon_name, s.approval_status, s.is_active
        FROM users u
        JOIN profiles p ON u.id = p.user_id
        LEFT JOIN user_roles ur ON u.id = ur.user_id
        LEFT JOIN salons s ON ur.salon_id = s.id
        WHERE p.user_type = 'salon_owner'
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($owners as $owner) {
        printf(
            "Owner: %-30s | Type: %-12s | Role: %-6s | Salon: %-20s | Status: %-10s | Active: %d\n",
            $owner['email'],
            $owner['user_type'],
            $owner['role'],
            $owner['salon_name'],
            $owner['approval_status'],
            $owner['is_active']
        );
    }

    echo "\n--- ALL SALONS ---\n";
    $salons = $db->query("SELECT id, name, approval_status, is_active FROM salons")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($salons as $s) {
        printf("ID: %s | Name: %-20s | Status: %-10s | Active: %d\n", $s['id'], $s['name'], $s['approval_status'], $s['is_active']);
    }

    echo "\n--- RECENT BOOKINGS ---\n";
    $bookings = $db->query("
        SELECT b.id, s.name as salon_name, b.status, b.booking_date, b.booking_time
        FROM bookings b
        JOIN salons s ON b.salon_id = s.id
        ORDER BY b.created_at DESC
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($bookings as $b) {
        printf("ID: %s | Salon: %-15s | Status: %-10s | Date: %s %s\n", $b['id'], $b['salon_name'], $b['status'], $b['booking_date'], $b['booking_time']);
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
