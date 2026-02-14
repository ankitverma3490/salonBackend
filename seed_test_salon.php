<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/plain');

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Auth.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "1. Connected to database successfully.\n";

    // Check if any salon exists
    $stmt = $db->query("SELECT COUNT(*) FROM salons");
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        echo "Salons already exist in the database. No seeding needed.\n";
        exit;
    }

    echo "2. No salons found. Seeding a test salon...\n";

    $salonId = Auth::generateUuid();
    $stmt = $db->prepare("
        INSERT INTO salons (id, name, slug, description, address, city, state, pincode, phone, email, is_active, approval_status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 'approved', NOW())
    ");

    $stmt->execute([
        $salonId,
        'Luxe Studio NYC',
        'luxe-studio-nyc',
        'A premium luxury salon experience in the heart of New York City.',
        '123 Fifth Avenue',
        'New York',
        'NY',
        '10001',
        '555-0123',
        'contact@luxestudionyc.com'
    ]);

    echo "3. Test salon 'Luxe Studio NYC' created successfully!\n";
    echo "ID: $salonId\n";
    echo "\nYou can now refresh your homepage to see the salon.";

} catch (Exception $e) {
    echo "\nERROR: " . $e->getMessage() . "\n";
}
