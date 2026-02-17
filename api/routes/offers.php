<?php
// Salon Offers routes

// Auto-migrate if column missing
try {
    $dbConn = $db->getConnection();
    $stmt = $dbConn->query("SHOW COLUMNS FROM salon_offers LIKE 'usage_count'");
    if (!$stmt->fetch()) {
        $dbConn->exec("ALTER TABLE salon_offers ADD COLUMN usage_count INT NOT NULL DEFAULT 0 AFTER max_usage");
    }

    // Explicit sync: update usage_count based on bookings table
    // This ensures that even if manual bookings were made or past bookings existed,
    // the count is always correct when the offers are accessed.
    $dbConn->exec("
        UPDATE salon_offers o 
        SET o.usage_count = (
            SELECT COUNT(*) FROM bookings b 
            WHERE TRIM(LOWER(b.coupon_code)) = TRIM(LOWER(o.code)) 
            AND b.salon_id = o.salon_id
        )
    ");
}
catch (Exception $e) {
// Silence error if migration fails
}

// GET /api/offers?salon_id=... - List all offers for a salon
if ($method === 'GET' && count($uriParts) === 1) {
    $salonId = $_GET['salon_id'] ?? null;
    if (!$salonId) {
        sendResponse(['error' => 'salon_id is required'], 400);
    }

    try {
        $stmt = $db->prepare("
            SELECT * FROM salon_offers 
            WHERE salon_id = ? 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$salonId]);
        $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        sendResponse(['offers' => $offers]);
    }
    catch (PDOException $e) {
        sendResponse(['error' => 'Query failed: ' . $e->getMessage()], 500);
    }
}

// POST /api/offers - Create new offer
if ($method === 'POST' && count($uriParts) === 1) {
    $userData = protectRoute(['owner', 'manager']);
    $data = getRequestBody();

    if (!isset($data['salon_id']) || !isset($data['title']) || !isset($data['code']) || !isset($data['type'])) {
        sendResponse(['error' => 'Missing required fields'], 400);
    }

    // Verify user has access to this salon
    protectRoute(['owner', 'manager'], null, $data['salon_id']);

    $offerId = Auth::generateUuid();

    try {
        $stmt = $db->prepare("
            INSERT INTO salon_offers (id, salon_id, title, description, code, type, value, max_usage, start_date, end_date, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $offerId,
            $data['salon_id'],
            $data['title'],
            $data['description'] ?? null,
            $data['code'],
            $data['type'],
            $data['value'] ?? 0,
            $data['max_usage'] ?? null,
            $data['start_date'] ?? null,
            $data['end_date'] ?? null,
            $data['status'] ?? 'active'
        ]);

        $stmt = $db->prepare("SELECT * FROM salon_offers WHERE id = ?");
        $stmt->execute([$offerId]);
        $offer = $stmt->fetch(PDO::FETCH_ASSOC);

        sendResponse(['offer' => $offer], 201);
    }
    catch (PDOException $e) {
        sendResponse(['error' => 'Failed to create offer: ' . $e->getMessage()], 500);
    }
}

// PUT /api/offers/:id - Update offer
if ($method === 'PUT' && count($uriParts) === 2) {
    $userData = protectRoute(['owner', 'manager']);
    $offerId = $uriParts[1];
    $data = getRequestBody();

    // Get offer to find salon_id
    $stmt = $db->prepare("SELECT salon_id FROM salon_offers WHERE id = ?");
    $stmt->execute([$offerId]);
    $offer = $stmt->fetch();

    if (!$offer) {
        sendResponse(['error' => 'Offer not found'], 404);
    }

    // Verify user has access to this salon
    protectRoute(['owner', 'manager'], null, $offer['salon_id']);

    // Dynamic update
    $fields = [];
    $params = [];
    $allowedFields = ['title', 'description', 'code', 'type', 'value', 'max_usage', 'start_date', 'end_date', 'status'];

    foreach ($data as $key => $value) {
        if (in_array($key, $allowedFields)) {
            $fields[] = "$key = ?";
            $params[] = $value;
        }
    }

    if (empty($fields)) {
        sendResponse(['error' => 'No valid fields provided'], 400);
    }

    $params[] = $offerId;
    try {
        $stmt = $db->prepare("UPDATE salon_offers SET " . implode(', ', $fields) . " WHERE id = ?");
        $stmt->execute($params);

        $stmt = $db->prepare("SELECT * FROM salon_offers WHERE id = ?");
        $stmt->execute([$offerId]);
        $updatedOffer = $stmt->fetch(PDO::FETCH_ASSOC);

        sendResponse(['offer' => $updatedOffer]);
    }
    catch (PDOException $e) {
        sendResponse(['error' => 'Update failed: ' . $e->getMessage()], 500);
    }
}

// DELETE /api/offers/:id - Delete offer
if ($method === 'DELETE' && count($uriParts) === 2) {
    $userData = protectRoute(['owner', 'manager']);
    $offerId = $uriParts[1];

    $stmt = $db->prepare("SELECT salon_id FROM salon_offers WHERE id = ?");
    $stmt->execute([$offerId]);
    $offer = $stmt->fetch();

    if (!$offer) {
        sendResponse(['error' => 'Offer not found'], 404);
    }

    protectRoute(['owner', 'manager'], null, $offer['salon_id']);

    try {
        $stmt = $db->prepare("DELETE FROM salon_offers WHERE id = ?");
        $stmt->execute([$offerId]);
        sendResponse(['message' => 'Offer deleted successfully']);
    }
    catch (PDOException $e) {
        sendResponse(['error' => 'Delete failed: ' . $e->getMessage()], 500);
    }
}

// GET /api/offers/:id/redemptions - Get redemption details
if ($method === 'GET' && count($uriParts) === 3 && $uriParts[2] === 'redemptions') {
    $offerId = $uriParts[1];

    try {
        // Find the offer first to get the code and salon_id
        $stmt = $db->prepare("SELECT code, salon_id FROM salon_offers WHERE id = ?");
        $stmt->execute([$offerId]);
        $offer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$offer) {
            sendResponse(['error' => 'Offer not found'], 404);
        }

        // Verify owner/manager clearance
        protectRoute(['owner', 'manager'], null, $offer['salon_id']);

        // Query bookings that used this coupon code
        // Join with users and profiles to get customer info
        $stmt = $db->prepare("
            SELECT 
                b.id as booking_id,
                b.booking_date,
                b.price_paid,
                b.discount_amount,
                u.email as customer_email,
                p.full_name as customer_name,
                p.phone as customer_phone
            FROM bookings b
            JOIN users u ON b.user_id = u.id
            LEFT JOIN profiles p ON u.id = p.user_id
            WHERE TRIM(LOWER(b.coupon_code)) = TRIM(LOWER(?)) AND b.salon_id = ?
            ORDER BY b.booking_date DESC
        ");
        $stmt->execute([$offer['code'], $offer['salon_id']]);
        $redemptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        sendResponse(['redemptions' => $redemptions]);
    }
    catch (PDOException $e) {
        sendResponse(['error' => 'Failed to fetch redemptions: ' . $e->getMessage()], 500);
    }
}

sendResponse(['error' => 'Offer route not found'], 404);
