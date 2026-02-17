<?php
// Validate coupon code
// Validate coupon code
if ($method === 'GET') {
    $code = $_GET['code'] ?? null;
    $salonId = $_GET['salon_id'] ?? null;

    // Handle /coupons/validate/{code} format
    if (isset($uriParts[1]) && $uriParts[1] === 'validate') {
        $code = urldecode($uriParts[2] ?? '');
    }
    $code = urldecode($code); // Ensure it's decoded even if from query param

    // DEBUG LOGGING
    $logMsg = date('[Y-m-d H:i:s] ') . "validate coupon > Code: " . json_encode($code) . ", SalonID: " . json_encode($salonId) . "\n";
    file_put_contents(__DIR__ . '/../../coupon_debug.log', $logMsg, FILE_APPEND);

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
            $failMsg = date('[Y-m-d H:i:s] ') . "validate coupon > Offer NOT found for code: $code in salon: $salonId\n";

            // Check if it exists but is inactive or expired
            $checkStmt = $db->prepare("SELECT * FROM salon_offers WHERE code = ? AND salon_id = ?");
            $checkStmt->execute([$code, $salonId]);
            $debugOffer = $checkStmt->fetch(PDO::FETCH_ASSOC);
            if ($debugOffer) {
                $failMsg .= "validate coupon > Found but invalid: " . json_encode($debugOffer) . "\n";
            }

            // Dump ALL offers for this salon to see what exists
            $allStmt = $db->prepare("SELECT code, is_active, start_date, end_date FROM salon_offers WHERE salon_id = ?");
            $allStmt->execute([$salonId]);
            $allOffers = $allStmt->fetchAll(PDO::FETCH_ASSOC);
            $failMsg .= "validate coupon > NO MATCH. Available offers: " . json_encode($allOffers) . "\n";

            file_put_contents(__DIR__ . '/../../coupon_debug.log', $failMsg, FILE_APPEND);

            sendResponse(['error' => 'Invalid or expired offer code'], 404);
        }

        // Check usage limits if applicable
        if ($offer['max_usage'] > 0 && $offer['usage_count'] >= $offer['max_usage']) {
            sendResponse(['error' => 'Offer usage limit reached'], 400);
        }

        // Map DB 'value' to 'discount_value' for frontend compatibility
        $offer['discount_value'] = $offer['value'];

        $successMsg = date('[Y-m-d H:i:s] ') . "validate coupon > SUCCESS. Returning: " . json_encode($offer) . "\n";
        file_put_contents(__DIR__ . '/../../coupon_debug.log', $successMsg, FILE_APPEND);

        sendResponse($offer);
    }
    catch (PDOException $e) {
        $errorMsg = date('[Y-m-d H:i:s] ') . "validate coupon > EXCEPTION: " . $e->getMessage() . "\n";
        file_put_contents(__DIR__ . '/../../coupon_debug.log', $errorMsg, FILE_APPEND);
        sendResponse(['error' => 'Validation failed: ' . $e->getMessage()], 500);
    }
}
