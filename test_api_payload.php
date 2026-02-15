<?php
require_once __DIR__ . '/Database.php';

$db = Database::getInstance()->getConnection();
$userId = '888a6dac-e54d-4ddb-91da-08af2e7f7396';

$stmt = $db->prepare("
    SELECT skin_type, allergy_records, medical_conditions, updated_at 
    FROM customer_salon_profiles 
    WHERE user_id = ? 
    ORDER BY updated_at DESC
");
$stmt->execute([$userId]);
$profiles = $stmt->fetchAll();

echo "Records for $userId: " . count($profiles) . "\n";

$consolidated = [
    'skin_type' => 'Not Specified',
    'allergies' => 'None Reported',
    'medical_conditions' => 'None Reported',
    'records_count' => count($profiles)
];

if (!empty($profiles)) {
    foreach ($profiles as $p) {
        if (!empty($p['skin_type'])) {
            $consolidated['skin_type'] = $p['skin_type'];
            break;
        }
    }

    $allAllergies = [];
    foreach ($profiles as $p) {
        if (!empty($p['allergy_records'])) {
            $parts = array_map('trim', explode(',', $p['allergy_records']));
            $allAllergies = array_merge($allAllergies, $parts);
        }
    }

    $uniqueAllergies = array_unique(array_filter($allAllergies));
    if (!empty($uniqueAllergies)) {
        $consolidated['allergies'] = implode(', ', $uniqueAllergies);
    }
}

echo "API PAYLOAD (simulated):\n";
echo json_encode(['profile' => $consolidated], JSON_PRETTY_PRINT) . "\n";
