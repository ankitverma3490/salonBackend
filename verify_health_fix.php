<?php
require_once __DIR__ . '/Database.php';

$userId = 'e41a7135-b1e9-4a15-82cd-9fec1c7eae43'; // User 'aman'

try {
    $db = Database::getInstance()->getConnection();

    echo "=== Testing Consolidated Health Profile for $userId ===\n";

    // Check if any profiles exist
    $stmt = $db->prepare("SELECT COUNT(*) FROM customer_salon_profiles WHERE user_id = ?");
    $stmt->execute([$userId]);
    echo "Existing record count: " . $stmt->fetchColumn() . "\n";

    // Simulate the API logic manually to verify
    $stmt = $db->prepare("
        SELECT skin_type, allergy_records, medical_conditions, updated_at 
        FROM customer_salon_profiles 
        WHERE user_id = ? 
        ORDER BY updated_at DESC
    ");
    $stmt->execute([$userId]);
    $profiles = $stmt->fetchAll();

    if (empty($profiles)) {
        echo "No profiles found in DB. Creating a test one...\n";
        $salonId = '9f9a5cfb-a3c4-40e4-a6a2-f5dbba2143b9';
        $stmt = $db->prepare("
            INSERT INTO customer_salon_profiles (id, user_id, salon_id, skin_type, allergy_records)
            VALUES (UUID(), ?, ?, 'Oily', 'Peanuts, Latex')
        ");
        $stmt->execute([$userId, $salonId]);
        echo "Test profile created. ✓\n";
    }
    else {
        echo "Latest Skin Type: " . ($profiles[0]['skin_type'] ?: 'N/A') . "\n";
        echo "Allergies: " . ($profiles[0]['allergy_records'] ?: 'N/A') . "\n";
    }

}
catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
