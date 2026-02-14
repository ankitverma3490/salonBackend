<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();

    echo "Seeding permissions...\n";
    $permissions = [
        ['248432dc-fae9-11f0-b172-a4f9334d99db', 'view_bookings', 'Can view salon bookings', 'bookings', '2026-01-27 00:29:27'],
        ['24843a63-fae9-11f0-b172-a4f9334d99db', 'manage_bookings', 'Can create, update, and cancel bookings', 'bookings', '2026-01-27 00:29:27'],
        ['24843b09-fae9-11f0-b172-a4f9334d99db', 'view_staff', 'Can view salon staff roster', 'staff', '2026-01-27 00:29:27'],
        ['24843b43-fae9-11f0-b172-a4f9334d99db', 'manage_staff', 'Can add, remove, or edit staff details', 'staff', '2026-01-27 00:29:27'],
        ['24843b7b-fae9-11f0-b172-a4f9334d99db', 'view_reports', 'Can view business revenue and analytics', 'reports', '2026-01-27 00:29:27'],
        ['24843bb6-fae9-11f0-b172-a4f9334d99db', 'manage_services', 'Can manage salon services and pricing', 'services', '2026-01-27 00:29:27'],
        ['24843bf2-fae9-11f0-b172-a4f9334d99db', 'track_attendance', 'Can check-in/out and view own attendance', 'attendance', '2026-01-27 00:29:27'],
        ['24843c3d-fae9-11f0-b172-a4f9334d99db', 'manage_attendance', 'Can view and edit everyone\'s attendance', 'attendance', '2026-01-27 00:29:27']
    ];

    $stmt = $db->prepare("INSERT IGNORE INTO permissions (id, name, description, category, created_at) VALUES (?, ?, ?, ?, ?)");
    foreach ($permissions as $p) {
        $stmt->execute($p);
    }

    echo "Seeding role_permissions...\n";
    $rolePermissions = [
        ['manager', '248432dc-fae9-11f0-b172-a4f9334d99db'],
        ['manager', '24843a63-fae9-11f0-b172-a4f9334d99db'],
        ['manager', '24843b09-fae9-11f0-b172-a4f9334d99db'],
        ['manager', '24843b43-fae9-11f0-b172-a4f9334d99db'],
        ['manager', '24843bb6-fae9-11f0-b172-a4f9334d99db'],
        ['manager', '24843bf2-fae9-11f0-b172-a4f9334d99db'],
        ['manager', '24843c3d-fae9-11f0-b172-a4f9334d99db'],
        ['owner', '248432dc-fae9-11f0-b172-a4f9334d99db'],
        ['owner', '24843a63-fae9-11f0-b172-a4f9334d99db'],
        ['owner', '24843b09-fae9-11f0-b172-a4f9334d99db'],
        ['owner', '24843b43-fae9-11f0-b172-a4f9334d99db'],
        ['owner', '24843b7b-fae9-11f0-b172-a4f9334d99db'],
        ['owner', '24843bb6-fae9-11f0-b172-a4f9334d99db'],
        ['owner', '24843bf2-fae9-11f0-b172-a4f9334d99db'],
        ['owner', '24843c3d-fae9-11f0-b172-a4f9334d99db'],
        ['staff', '248432dc-fae9-11f0-b172-a4f9334d99db'],
        ['staff', '24843bf2-fae9-11f0-b172-a4f9334d99db']
    ];

    $stmt = $db->prepare("INSERT IGNORE INTO role_permissions (role, permission_id) VALUES (?, ?)");
    foreach ($rolePermissions as $rp) {
        $stmt->execute($rp);
    }

    echo "Seeding complete.\n";
}
catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
