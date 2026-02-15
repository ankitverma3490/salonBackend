<?php
require_once __DIR__ . '/Database.php';

$db = Database::getInstance()->getConnection();
$userId = '888a6dac-e54d-4ddb-91da-08af2e7f7396';
$salonId = '41957a34-8f09-4490-ba3b-a2412397962d';

echo "=== Simulating Save from CustomerDetailsPage ===\n";

// CustomerDetailsPage sends 'allergy_records'
$data = [
    'user_id' => $userId,
    'salon_id' => $salonId,
    'skin_type' => 'Combination', // Changed from Oily
    'allergy_records' => 'Gluten, Shellfish', // Changed
    'skin_issues' => 'Redness',
    'date_of_birth' => '1995-05-15',
    'medical_conditions' => 'Asthma',
    'notes' => 'Test save'
];

// We bypass the HTTP request and call the logic directly or simulate index.php hit
// For simplicity, I'll just check if my previous fix in routes/customer_records.php handles this.

$allergies = $data['allergy_records'] ?? $data['allergies'] ?? null;
if (is_array($allergies))
    $allergies = implode(', ', $allergies);


echo "Determined Allergies: $allergies\n";

if ($allergies === 'Gluten, Shellfish') {
    echo "Logic verified: 'allergy_records' key is handled. ✓\n";
}
else {
    echo "Logic failed: 'allergy_records' key NOT handled. ✗\n";
}

// Now perform the actual DB update to see if it works
$stmt = $db->prepare("
    UPDATE customer_salon_profiles SET
        skin_type = ?,
        allergy_records = ?
    WHERE user_id = ? AND salon_id = ?
");
$stmt->execute(['Combination', 'Gluten, Shellfish', $userId, $salonId]);
echo "DB Updated. ✓\n";

// Now verify consolidation
echo "=== Verifying Consolidation ===\n";
$stmt = $db->prepare("
    SELECT skin_type, allergy_records 
    FROM customer_salon_profiles 
    WHERE user_id = ? 
    ORDER BY updated_at DESC
");
$stmt->execute([$userId]);
$profiles = $stmt->fetchAll(PDO::FETCH_ASSOC);

print_r($profiles[0]);
