<?php
// Customer Records routes

// GET /api/customer_records/:userId/salon/:salonId - Get custom profile
if ($method === 'GET' && count($uriParts) === 4 && $uriParts[2] === 'salon') {
    $userData = Auth::getUserFromToken();
    if (!$userData) {
        sendResponse(['error' => 'Unauthorized'], 401);
    }

    $userId = $uriParts[1];
    $salonId = $uriParts[3];

    // Check permission (user themselves or salon staff)
    $hasAccess = ($userData['user_id'] === $userId);
    if (!$hasAccess) {
        $stmt = $db->prepare("SELECT id FROM user_roles WHERE user_id = ? AND salon_id = ?");
        $stmt->execute([$userData['user_id'], $salonId]);
        $hasAccess = (bool)$stmt->fetch();
    }

    if (!$hasAccess) {
        sendResponse(['error' => 'Forbidden'], 403);
    }

    $stmt = $db->prepare("SELECT * FROM customer_salon_profiles WHERE user_id = ? AND salon_id = ?");
    $stmt->execute([$userId, $salonId]);
    $profile = $stmt->fetch();

    sendResponse(['profile' => $profile]);
}

// Ensure tables exist
$db->exec("CREATE TABLE IF NOT EXISTS customer_salon_profiles (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    user_id VARCHAR(36) NOT NULL,
    salon_id VARCHAR(36) NOT NULL,
    date_of_birth DATE,
    skin_type VARCHAR(50),
    skin_issues TEXT,
    allergy_records TEXT,
    medical_conditions TEXT,
    notes TEXT,
    concern_photo_url VARCHAR(255),
    concern_photo_public_id VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_salon_profile (user_id, salon_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Ensure all columns exist (Migration Helper)
$columns = $db->query("SHOW COLUMNS FROM customer_salon_profiles")->fetchAll(PDO::FETCH_COLUMN);
$requiredColumns = [
    'medical_conditions' => "ALTER TABLE customer_salon_profiles ADD COLUMN medical_conditions TEXT",
    'notes' => "ALTER TABLE customer_salon_profiles ADD COLUMN notes TEXT",
    'concern_photo_url' => "ALTER TABLE customer_salon_profiles ADD COLUMN concern_photo_url VARCHAR(255)",
    'concern_photo_public_id' => "ALTER TABLE customer_salon_profiles ADD COLUMN concern_photo_public_id VARCHAR(255)"
];

foreach ($requiredColumns as $col => $sql) {
    if (!in_array($col, $columns)) {
        $db->exec($sql);
    }
}

$db->exec("CREATE TABLE IF NOT EXISTS treatment_records (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    booking_id VARCHAR(36),
    user_id VARCHAR(36) NOT NULL,
    salon_id VARCHAR(36) NOT NULL,
    service_name_manual VARCHAR(255),
    record_date DATE,
    treatment_details TEXT,
    products_used TEXT,
    skin_reaction TEXT,
    improvement_notes TEXT,
    recommended_next_treatment TEXT,
    post_treatment_instructions TEXT,
    follow_up_reminder_date DATE,
    marketing_notes TEXT,
    before_photo_url TEXT,
    before_photo_public_id VARCHAR(255),
    after_photo_url TEXT,
    after_photo_public_id VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Ensure all columns exist for treatment records (Migration Helper)
$tColumns = $db->query("SHOW COLUMNS FROM treatment_records")->fetchAll(PDO::FETCH_COLUMN);
$requiredTColumns = [
    'marketing_notes' => "ALTER TABLE treatment_records ADD COLUMN marketing_notes TEXT",
    'before_photo_url' => "ALTER TABLE treatment_records ADD COLUMN before_photo_url VARCHAR(255)",
    'before_photo_public_id' => "ALTER TABLE treatment_records ADD COLUMN before_photo_public_id VARCHAR(255)",
    'after_photo_url' => "ALTER TABLE treatment_records ADD COLUMN after_photo_url VARCHAR(255)",
    'after_photo_public_id' => "ALTER TABLE treatment_records ADD COLUMN after_photo_public_id VARCHAR(255)",
    'service_name_manual' => "ALTER TABLE treatment_records ADD COLUMN service_name_manual VARCHAR(255)",
    'follow_up_reminder_date' => "ALTER TABLE treatment_records ADD COLUMN follow_up_reminder_date DATE",
    'record_date' => "ALTER TABLE treatment_records ADD COLUMN record_date DATE"
];

foreach ($requiredTColumns as $col => $sql) {
    if (!in_array($col, $tColumns)) {
        try {
            $db->exec($sql);
        }
        catch (Exception $e) {
        }
    }
}

// Add unique constraint to treatment_records if missing
try {
    $db->exec("ALTER TABLE treatment_records ADD UNIQUE INDEX idx_unique_booking (booking_id)");
}
catch (Exception $e) {
// Index already exists probably
}

// POST /api/customer_records - Create or update profile
if ($method === 'POST' && count($uriParts) === 1) {
    $userData = Auth::getUserFromToken();
    if (!$userData) {
        sendResponse(['error' => 'Unauthorized'], 401);
    }

    $data = getRequestBody();
    $userId = $data['user_id'] ?? null;
    $salonId = $data['salon_id'] ?? null;

    if (!$userId || !$salonId) {
        sendResponse(['error' => 'User ID and Salon ID are required'], 400);
    }

    // Check permission (user themselves OR salon staff)
    $hasAccess = ($userData['user_id'] === $userId);
    if (!$hasAccess) {
        $stmt = $db->prepare("SELECT id FROM user_roles WHERE user_id = ? AND salon_id = ?");
        $stmt->execute([$userData['user_id'], $salonId]);
        $hasAccess = (bool)$stmt->fetch();
    }

    if (!$hasAccess) {
        // Bypass for super_admin
        $stmt = $db->prepare("SELECT 1 FROM platform_admins WHERE user_id = ? AND is_active = 1");
        $stmt->execute([$userData['user_id']]);
        if ($stmt->fetch()) {
            $hasAccess = true;
        }
    }

    if (!$hasAccess) {
        sendResponse(['error' => 'Forbidden'], 403);
    }

    // Prepare arrays if they are arrays
    $skinIssues = $data['skin_issues'] ?? null;
    if (is_array($skinIssues))
        $skinIssues = implode(', ', $skinIssues);

    $allergies = $data['allergies'] ?? null;
    if (is_array($allergies))
        $allergies = implode(', ', $allergies);

    $conditions = $data['medical_conditions'] ?? null;
    if (is_array($conditions))
        $conditions = implode(', ', $conditions);

    $stmt = $db->prepare("
        INSERT INTO customer_salon_profiles (id, user_id, salon_id, date_of_birth, skin_type, skin_issues, allergy_records, medical_conditions, notes, concern_photo_url, concern_photo_public_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            date_of_birth = VALUES(date_of_birth),
            skin_type = VALUES(skin_type),
            skin_issues = VALUES(skin_issues),
            allergy_records = VALUES(allergy_records),
            medical_conditions = VALUES(medical_conditions),
            notes = VALUES(notes),
            concern_photo_url = VALUES(concern_photo_url),
            concern_photo_public_id = VALUES(concern_photo_public_id)
    ");

    $stmt->execute([
        Auth::generateUuid(), // Keep Auth::generateUuid() for the ID
        $userId,
        $salonId,
        $data['date_of_birth'] ?? null,
        $data['skin_type'] ?? null,
        $skinIssues,
        $allergies,
        $conditions,
        $data['notes'] ?? null,
        $data['concern_photo_url'] ?? null,
        $data['concern_photo_public_id'] ?? null
    ]);

    sendResponse(['success' => true]);
}

// Routes for treatment records
// GET /api/customer_records/treatments/:bookingId - Get treatment record
if ($method === 'GET' && count($uriParts) === 3 && $uriParts[1] === 'treatments') {
    $bookingId = $uriParts[2];
    $stmt = $db->prepare("SELECT * FROM treatment_records WHERE booking_id = ?");
    $stmt->execute([$bookingId]);
    $record = $stmt->fetch();
    sendResponse(['record' => $record]);
}

// POST /api/customer_records/treatments - Create or update treatment record
if ($method === 'POST' && count($uriParts) === 2 && $uriParts[1] === 'treatments') {
    $userData = Auth::getUserFromToken();
    if (!$userData) {
        sendResponse(['error' => 'Unauthorized'], 401);
    }

    $data = getRequestBody();
    $bookingId = $data['booking_id'] ?? null;
    $salonId = $data['salon_id'] ?? null;
    $userId = $data['user_id'] ?? null;

    if (!$bookingId && (!$salonId || !$userId)) {
        sendResponse(['error' => 'Booking ID or (Salon ID and User ID) are required'], 400);
    }

    if ($bookingId) {
        // Fetch booking to get salon_id and user_id if not provided
        $stmt = $db->prepare("SELECT salon_id, user_id FROM bookings WHERE id = ?");
        $stmt->execute([$bookingId]);
        $booking = $stmt->fetch();

        if (!$booking) {
            sendResponse(['error' => 'Booking not found'], 404);
        }
        $salonId = $booking['salon_id'];
        $userId = $booking['user_id'];
    }

    // Check permission
    $stmt = $db->prepare("SELECT id FROM user_roles WHERE user_id = ? AND salon_id = ?");
    $stmt->execute([$userData['user_id'], $salonId]);
    $hasAccess = (bool)$stmt->fetch();

    if (!$hasAccess) {
        // Bypass for super_admin
        $stmt = $db->prepare("SELECT 1 FROM platform_admins WHERE user_id = ? AND is_active = 1");
        $stmt->execute([$userData['user_id']]);
        $hasAccess = (bool)$stmt->fetch();
    }

    if (!$hasAccess) {
        sendResponse(['error' => 'Forbidden'], 403);
    }

    $recordId = $data['id'] ?? Auth::generateUuid();

    // Check if record exists for this booking to update specific record
    if ($bookingId) {
        $stmt = $db->prepare("SELECT id FROM treatment_records WHERE booking_id = ?");
        $stmt->execute([$bookingId]);
        $existing = $stmt->fetch();
        if ($existing) {
            $recordId = $existing['id'];
        }
    }

    $stmt = $db->prepare("
        INSERT INTO treatment_records (
            id, booking_id, user_id, salon_id, service_name_manual, record_date, treatment_details, products_used, 
            skin_reaction, improvement_notes, recommended_next_treatment, 
            post_treatment_instructions, follow_up_reminder_date, marketing_notes,
            before_photo_url, before_photo_public_id, after_photo_url, after_photo_public_id
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            service_name_manual = VALUES(service_name_manual),
            record_date = VALUES(record_date),
            treatment_details = VALUES(treatment_details),
            products_used = VALUES(products_used),
            skin_reaction = VALUES(skin_reaction),
            improvement_notes = VALUES(improvement_notes),
            recommended_next_treatment = VALUES(recommended_next_treatment),
            post_treatment_instructions = VALUES(post_treatment_instructions),
            follow_up_reminder_date = VALUES(follow_up_reminder_date),
            marketing_notes = VALUES(marketing_notes),
            before_photo_url = VALUES(before_photo_url),
            before_photo_public_id = VALUES(before_photo_public_id),
            after_photo_url = VALUES(after_photo_url),
            after_photo_public_id = VALUES(after_photo_public_id)
    ");

    $stmt->execute([
        $recordId,
        $bookingId,
        $userId,
        $salonId,
        $data['service_name_manual'] ?? null,
        $data['record_date'] ?? null,
        $data['treatment_details'] ?? null,
        $data['products_used'] ?? null,
        $data['skin_reaction'] ?? null,
        $data['improvement_notes'] ?? null,
        $data['recommended_next_treatment'] ?? null,
        $data['post_treatment_instructions'] ?? null,
        $data['follow_up_reminder_date'] ?? null,
        $data['marketing_notes'] ?? null,
        $data['before_photo_url'] ?? null,
        $data['before_photo_public_id'] ?? null,
        $data['after_photo_url'] ?? null,
        $data['after_photo_public_id'] ?? null
    ]);

    sendResponse(['success' => true]);
}

// GET /api/customer_records/:userId/treatments - Get all treatments for a user
if ($method === 'GET' && count($uriParts) === 3 && $uriParts[2] === 'treatments') {
    $userId = $uriParts[1];
    $salonId = $_GET['salon_id'] ?? null;

    $query = "SELECT tr.*, s.name as service_name, b.booking_date 
              FROM treatment_records tr
              LEFT JOIN bookings b ON tr.booking_id = b.id
              LEFT JOIN services s ON b.service_id = s.id
              WHERE tr.user_id = ?";
    $params = [$userId];

    if ($salonId) {
        $query .= " AND tr.salon_id = ?";
        $params[] = $salonId;
    }

    $query .= " ORDER BY COALESCE(b.booking_date, tr.record_date, tr.created_at) DESC";

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $treatments = $stmt->fetchAll();

    sendResponse(['treatments' => $treatments]);
}

// GET /api/customer_records/transformations - Get all public transformations
if ($method === 'GET' && count($uriParts) === 2 && $uriParts[1] === 'transformations') {
    try {
        $stmt = $db->prepare("
            SELECT 
                tr.id, 
                tr.before_photo_url as before_image, 
                tr.after_photo_url as after_image,
                tr.treatment_details as comment,
                COALESCE(p.full_name, SUBSTRING_INDEX(u.email, '@', 1)) as customer_name,
                s.name as treatment_name,
                CONCAT(s.duration_minutes, ' Minutes') as duration,
                COALESCE(r.rating, 5) as rating
            FROM treatment_records tr
            JOIN users u ON tr.user_id = u.id
            LEFT JOIN profiles p ON tr.user_id = p.user_id
            JOIN bookings b ON tr.booking_id = b.id
            JOIN services s ON b.service_id = s.id
            LEFT JOIN booking_reviews r ON tr.booking_id = r.booking_id
            WHERE tr.before_photo_url IS NOT NULL AND tr.after_photo_url IS NOT NULL
            ORDER BY tr.created_at DESC
        ");
        $stmt->execute();
        $transformations = $stmt->fetchAll();
        sendResponse(['transformations' => $transformations]);
    }
    catch (Exception $e) {
        sendResponse(['error' => 'Failed to fetch transformations: ' . $e->getMessage()], 500);
    }
}

sendResponse(['error' => 'Customer records route not found'], 404);
