<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth_middleware.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$auth = authMiddleware($pdo);
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

try {
    // GET /api/customer_records/{userId}/profile - Get consolidated health profile
    if ($method === 'GET' && isset($pathParts[3]) && $pathParts[4] === 'profile') {
        $userId = $pathParts[3];

        // Permission check
        if ($auth['user_id'] !== $userId) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Forbidden']);
            exit;
        }

        // Consolidate health data
        $stmt = $pdo->prepare("
            SELECT skin_type, allergy_records, medical_conditions, updated_at 
            FROM customer_salon_profiles 
            WHERE user_id = ? 
            ORDER BY updated_at DESC
        ");
        $stmt->execute([$userId]);
        $profiles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $consolidated = [
            'skin_type' => 'Not Specified',
            'allergies' => 'None Reported',
            'medical_conditions' => 'None Reported',
            'records_count' => count($profiles)
        ];

        if (!empty($profiles)) {
            foreach ($profiles as $p) {
                if (!empty($p['skin_type']) && $consolidated['skin_type'] === 'Not Specified') {
                    $consolidated['skin_type'] = $p['skin_type'];
                }
            }

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

        echo json_encode(['success' => true, 'data' => ['profile' => $consolidated]]);
        exit;
    }

    // GET /api/customer_records/{userId}/salon/{salonId} - Get customer profile for a salon
    if ($method === 'GET' && isset($pathParts[3]) && $pathParts[4] === 'salon' && isset($pathParts[5])) {
        $userId = $pathParts[3];
        $salonId = $pathParts[5];

        $stmt = $pdo->prepare("
            SELECT * FROM customer_salon_profiles 
            WHERE user_id = ? AND salon_id = ?
        ");
        $stmt->execute([$userId, $salonId]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($profile) {
            // Mapping allergy_records to allergies for JSON backward compatibility if needed
            $profile['allergies'] = json_decode($profile['allergy_records'] ?? '[]');
            $profile['skin_issues'] = json_decode($profile['skin_issues'] ?? '[]');
            $profile['medical_conditions'] = json_decode($profile['medical_conditions'] ?? '[]');
        }

        echo json_encode(['success' => true, 'data' => $profile]);
        exit;
    }

    // POST /api/customer_records - Create/Update customer profile
    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $userId = $data['user_id'] ?? null;
        $salonId = $data['salon_id'] ?? null;

        if (!$userId || !$salonId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'user_id and salon_id required']);
            exit;
        }

        // Check if profile exists
        $stmt = $pdo->prepare("SELECT id FROM customer_salon_profiles WHERE user_id = ? AND salon_id = ?");
        $stmt->execute([$userId, $salonId]);
        $existing = $stmt->fetch();

        // Convert arrays to comma-separated strings for DB compatibility with the new schema (which uses TEXT for these)
        // OR keep as JSON if we strictly want JSON. The new schema seems to use TEXT and the routed API uses implode.
        $skinIssues = $data['skin_issues'] ?? [];
        if (is_array($skinIssues))
            $skinIssues = implode(', ', $skinIssues);

        $allergies = $data['allergy_records'] ?? $data['allergies'] ?? [];
        if (is_array($allergies))
            $allergies = implode(', ', $allergies);

        $medicalConditions = $data['medical_conditions'] ?? [];
        if (is_array($medicalConditions))
            $medicalConditions = implode(', ', $medicalConditions);

        if ($existing) {
            // Update
            $stmt = $pdo->prepare("
                UPDATE customer_salon_profiles SET
                    date_of_birth = ?,
                    skin_type = ?,
                    skin_issues = ?,
                    allergy_records = ?,
                    medical_conditions = ?,
                    notes = ?,
                    updated_at = NOW()
                WHERE user_id = ? AND salon_id = ?
            ");
            $stmt->execute([
                (!empty($data['date_of_birth']) ? $data['date_of_birth'] : null),
                $data['skin_type'] ?? null,
                $skinIssues,
                $allergies,
                $medicalConditions,
                $data['notes'] ?? null,
                $userId,
                $salonId
            ]);

            echo json_encode(['success' => true, 'message' => 'Profile updated']);
        }
        else {
            // Insert
            $stmt = $pdo->prepare("
                INSERT INTO customer_salon_profiles 
                (id, user_id, salon_id, date_of_birth, skin_type, skin_issues, allergy_records, medical_conditions, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)),
                $userId,
                $salonId,
                (!empty($data['date_of_birth']) ? $data['date_of_birth'] : null),
                $data['skin_type'] ?? null,
                $skinIssues,
                $allergies,
                $medicalConditions,
                $data['notes'] ?? null
            ]);

            echo json_encode(['success' => true, 'message' => 'Profile created']);
        }
        exit;
    }

    // GET /api/customer_records/treatments/{bookingId} - Get treatment record
    if ($method === 'GET' && isset($pathParts[3]) && $pathParts[3] === 'treatments' && isset($pathParts[4])) {
        $bookingId = $pathParts[4];

        $stmt = $pdo->prepare("SELECT * FROM treatment_records WHERE booking_id = ?");
        $stmt->execute([$bookingId]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($record) {
            $record['products_used'] = json_decode($record['products_used'] ?? '[]');
            $record['observations'] = json_decode($record['observations'] ?? '{}');
        }

        echo json_encode(['success' => true, 'data' => $record]);
        exit;
    }

    // POST /api/customer_records/treatments - Create treatment record
    if ($method === 'POST' && isset($pathParts[3]) && $pathParts[3] === 'treatments') {
        $data = json_decode(file_get_contents('php://input'), true);

        $bookingId = $data['booking_id'] ?? null;
        $userId = $data['user_id'] ?? null;
        $salonId = $data['salon_id'] ?? null;

        if (!$bookingId || !$userId || !$salonId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'booking_id, user_id, and salon_id required']);
            exit;
        }

        $productsUsed = json_encode($data['products_used'] ?? []);
        $observations = json_encode($data['observations'] ?? new stdClass());

        // Check if record exists
        $stmt = $pdo->prepare("SELECT id FROM treatment_records WHERE booking_id = ?");
        $stmt->execute([$bookingId]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Update
            $stmt = $pdo->prepare("
                UPDATE treatment_records SET
                    treatment_details = ?,
                    products_used = ?,
                    skin_reaction = ?,
                    improvement_notes = ?,
                    observations = ?,
                    next_treatment_recommendation = ?,
                    follow_up_date = ?,
                    staff_notes = ?,
                    updated_at = NOW()
                WHERE booking_id = ?
            ");
            $stmt->execute([
                $data['treatment_details'] ?? null,
                $productsUsed,
                $data['skin_reaction'] ?? null,
                $data['improvement_notes'] ?? null,
                $observations,
                $data['next_treatment_recommendation'] ?? null,
                (!empty($data['follow_up_date']) ? $data['follow_up_date'] : null),
                $data['staff_notes'] ?? null,
                $bookingId
            ]);

            echo json_encode(['success' => true, 'message' => 'Treatment record updated']);
        }
        else {
            // Insert
            $stmt = $pdo->prepare("
                INSERT INTO treatment_records 
                (booking_id, user_id, salon_id, treatment_details, products_used, skin_reaction, 
                 improvement_notes, observations, next_treatment_recommendation, follow_up_date, staff_notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $bookingId,
                $userId,
                $salonId,
                $data['treatment_details'] ?? null,
                $productsUsed,
                $data['skin_reaction'] ?? null,
                $data['improvement_notes'] ?? null,
                $observations,
                $data['next_treatment_recommendation'] ?? null,
                (!empty($data['follow_up_date']) ? $data['follow_up_date'] : null),
                $data['staff_notes'] ?? null
            ]);

            echo json_encode(['success' => true, 'message' => 'Treatment record created']);
        }
        exit;
    }

    // GET /api/customer_records/{userId}/treatments - Get all treatments for a user
    if ($method === 'GET' && isset($pathParts[3]) && $pathParts[4] === 'treatments') {
        $userId = $pathParts[3];
        $salonId = $_GET['salon_id'] ?? null;

        $query = "
            SELECT tr.*, b.booking_date, b.service_name 
            FROM treatment_records tr
            LEFT JOIN bookings b ON tr.booking_id = b.id
            WHERE tr.user_id = ?
        ";
        $params = [$userId];

        if ($salonId) {
            $query .= " AND tr.salon_id = ?";
            $params[] = $salonId;
        }

        $query .= " ORDER BY b.booking_date DESC";

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($records as &$record) {
            $record['products_used'] = json_decode($record['products_used'] ?? '[]');
            $record['observations'] = json_decode($record['observations'] ?? '{}');
        }

        echo json_encode(['success' => true, 'data' => $records]);
        exit;
    }

    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Endpoint not found']);

}
catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
