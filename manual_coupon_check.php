<?php
// Test script to trigger coupon validation
$url = 'http://localhost/salon-saas/backend/api/coupons/validate/TESTCODE?salon_id=test_salon_id';

// Try to detect actual salon ID from database if possible, or just use a placeholder
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT id FROM salons LIMIT 1");
    $salon = $stmt->fetch(PDO::FETCH_ASSOC);
    $salonId = $salon['id'] ?? 'unknown';

    // Get a valid coupon code if exists
    $stmt = $db->prepare("SELECT code FROM salon_offers WHERE salon_id = ? LIMIT 1");
    $stmt->execute([$salonId]);
    $offer = $stmt->fetch(PDO::FETCH_ASSOC);
    $code = $offer['code'] ?? 'INVALID123';

    echo "Testing with Salon ID: $salonId and Code: $code\n";

    // Simulate the API call logic directly since we can't reliably curl localhost without port
    // We already know the logic in coupons.php, let's just run it here to verify

    echo "Running query logic...\n";
    $stmt = $db->prepare("
        SELECT * FROM salon_offers 
        WHERE TRIM(LOWER(code)) = TRIM(LOWER(?)) 
        AND salon_id = ? 
        AND is_active = 1
        AND (start_date IS NULL OR start_date <= CURRENT_TIMESTAMP)
        AND (end_date IS NULL OR end_date >= CURRENT_TIMESTAMP)
    ");
    $stmt->execute([$code, $salonId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        echo "SUCCESS: Coupon found!\n";
        print_r($result);
    }
    else {
        echo "FAILURE: Coupon NOT found.\n";

        // Debug
        $stmt = $db->prepare("SELECT * FROM salon_offers WHERE code = ? AND salon_id = ?");
        $stmt->execute([$code, $salonId]);
        $debug = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($debug) {
            echo "DEBUG: Coupon exists but failed criteria:\n";
            print_r($debug);
            echo "Current Time: " . date('Y-m-d H:i:s') . "\n";
        }
        else {
            echo "DEBUG: Coupon does not exist in DB.\n";
        }
    }


}
catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
