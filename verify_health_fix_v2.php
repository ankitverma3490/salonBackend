<?php
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();

    // Get a real user ID
    $stmt = $db->query("SELECT id, email FROM users LIMIT 1");
    $user = $stmt->fetch();
    if (!$user) {
        die("No users found in database to test with.\n");
    }
    $userId = $user['id'];
    echo "Using test user: " . $user['email'] . " ($userId)\n";

    echo "=== Testing Consolidated Health Profile ===\n";

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
        // Need a salon
        $salonId = $db->query("SELECT id FROM salons LIMIT 1")->fetchColumn();
        if (!$salonId) {
            die("No salons found to link test profile.\n");
        }
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
    echo "Error: " . $e->getMessage() . "\n";
}
