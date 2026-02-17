<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

$db = Database::getInstance()->getConnection();

// Test case 2: Salon with specialists
$salonId = '41957a34-8f09-4490-ba3b-a2412397962d'; // Salon for Bhavna
$bhavnaServiceId = '3b50a0c5-fc64-4158-9833-774b1d4844e5';
$otherServiceId = 'some-other-service';

echo "--- TESTING SPECIALIST (BHAVNA) ---\n";

foreach ([$bhavnaServiceId, $otherServiceId] as $sid) {
    echo "Testing for service: $sid\n";
    $query = "
        SELECT s.*
        FROM staff_profiles s
        WHERE s.salon_id = ? AND s.is_active = 1
        AND (s.id IN (SELECT staff_id FROM staff_services WHERE service_id = ?) OR s.id NOT IN (SELECT staff_id FROM staff_services))
    ";
    $stmt = $db->prepare($query);
    $stmt->execute([$salonId, $sid]);
    $staff = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Found " . count($staff) . " staff members.\n";
    foreach ($staff as $s) {
        echo "- " . $s['display_name'] . "\n";
    }
    echo "\n";
}
