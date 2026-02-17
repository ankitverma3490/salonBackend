<?php
// Validate coupon code
if ($method === 'GET' && count($uriParts) === 1) {
    $code = $_GET['code'] ?? null;
    $salonId = $_GET['salon_id'] ?? null;

    if (!$code || !$salonId) {
        sendResponse(['error' => 'Code and salon_id are required'], 400);
    }

    try {
        $stmt = $db->prepare("
            SELECT * FROM salon_offers 
            WHERE TRIM(LOWER(code)) = TRIM(LOWER(?)) 
            AND salon_id = ? 
            AND is_active = 1
            AND (start_date IS NULL OR start_date <= CURRENT_TIMESTAMP)
            AND (end_date IS NULL OR end_date >= CURRENT_TIMESTAMP)
        ");
        $stmt->execute([$code, $salonId]);
        $offer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$offer) {
            sendResponse(['error' => 'Invalid or expired offer code'], 404);
        }

        // Check usage limits if applicable
        if ($offer['max_usage'] > 0 && $offer['usage_count'] >= $offer['max_usage']) {
            sendResponse(['error' => 'Offer usage limit reached'], 400);
        }

        sendResponse($offer);
    }
    catch (PDOException $e) {
        sendResponse(['error' => 'Validation failed: ' . $e->getMessage()], 500);
    }
}
