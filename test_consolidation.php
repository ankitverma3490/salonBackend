<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Auth.php';

// Mock the request for routing
$method = 'GET';
$uriParts = ['customer_records', 'e41a7135-b1e9-4a15-82cd-9fec1c7eae43', 'profile'];

// Helper to send response (mocked)
function sendResponse($data, $status = 200)
{
    echo "STATUS: $status\n";
    echo "DATA: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
    exit;
}

// Mock Auth::getUserFromToken to return the test user
class AuthMock
{
    public static function getUserFromToken()
    {
        return ['user_id' => 'e41a7135-b1e9-4a15-82cd-9fec1c7eae43'];
    }
}

// Intercept Auth calls
// ... Actually, I'll just copy the logic from customer_records.php to this script for testing.
$db = Database::getInstance()->getConnection();

$userId = 'e41a7135-b1e9-4a15-82cd-9fec1c7eae43';

echo "Testing consolidation logic for $userId\n";

$stmt = $db->prepare("
    SELECT skin_type, allergy_records, medical_conditions, updated_at 
    FROM customer_salon_profiles 
    WHERE user_id = ? 
    ORDER BY updated_at DESC
");
$stmt->execute([$userId]);
$profiles = $stmt->fetchAll();

echo "Raw records found: " . count($profiles) . "\n";

$consolidated = [
    'skin_type' => 'Not Specified',
    'allergies' => 'None Reported',
    'medical_conditions' => 'None Reported',
    'records_count' => count($profiles)
];

if (!empty($profiles)) {
    // Take latest skin type
    foreach ($profiles as $p) {
        if (!empty($p['skin_type'])) {
            $consolidated['skin_type'] = $p['skin_type'];
            break;
        }
    }

    // Aggregate unique allergies
    $allAllergies = [];
    $allConditions = [];
    foreach ($profiles as $p) {
        if (!empty($p['allergy_records'])) {
            $parts = array_map('trim', explode(',', $p['allergy_records']));
            $allAllergies = array_merge($allAllergies, $parts);
        }
        if (!empty($p['medical_conditions'])) {
            $parts = array_map('trim', explode(',', $p['medical_conditions']));
            $allConditions = array_merge($allConditions, $parts);
        }
    }

    $uniqueAllergies = array_unique(array_filter($allAllergies));
    if (!empty($uniqueAllergies)) {
        $consolidated['allergies'] = implode(', ', $uniqueAllergies);
    }

    $uniqueConditions = array_unique(array_filter($allConditions));
    if (!empty($uniqueConditions)) {
        $consolidated['medical_conditions'] = implode(', ', $uniqueConditions);
    }
}

echo "CONSOLIDATED RESULT:\n";
print_r($consolidated);
