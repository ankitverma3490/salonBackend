<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

$db = Database::getInstance()->getConnection();

$salonId = 'cdbde42d-d7f2-4adf-b388-78be5fd01551';
$serviceId = 'any-random-service-id';

echo "Testing staff lookup for salon: $salonId and service: $serviceId\n";

$query = "
    SELECT s.*
    FROM staff_profiles s
    WHERE s.salon_id = ? AND s.is_active = 1
";
$params = [$salonId];

if ($serviceId) {
    $query .= " AND (s.id IN (SELECT staff_id FROM staff_services WHERE service_id = ?) OR s.id NOT IN (SELECT staff_id FROM staff_services))";
    $params[] = $serviceId;
}

$stmt = $db->prepare($query);
$stmt->execute($params);
$staff = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($staff) . " staff members.\n";
foreach ($staff as $s) {
    echo "- " . $s['display_name'] . " (ID: " . $s['id'] . ")\n";
}
