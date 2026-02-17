<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();

    // Check specific coupon if provided
    echo "<h1>Checking Offers</h1>";
    $code = 'SUPER40';
    $salonId = 'cdbde42d-d7f2-4adf-b388-78be5fd01551';

    echo "<h2>Looking for Code: $code in Salon: $salonId</h2>";

    // 1. Direct Match
    $stmt = $db->prepare("SELECT * FROM salon_offers WHERE code = ? AND salon_id = ?");
    $stmt->execute([$code, $salonId]);
    $direct = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($direct) {
        echo "<h3 style='color:green'>Found with Direct Match!</h3>";
        print_r($direct);
    }
    else {
        echo "<h3 style='color:red'>Not found with Direct Match</h3>";
    }

    // 2. Case Insensitive Match
    $stmt = $db->prepare("SELECT * FROM salon_offers WHERE LOWER(code) = LOWER(?) AND salon_id = ?");
    $stmt->execute([$code, $salonId]);
    $loose = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($loose) {
        echo "<h3 style='color:orange'>Found with Case-Insensitive Match!</h3>";
        print_r($loose);
    }

    // 3. List all offers for this salon
    echo "<h2>All Offers for Salon $salonId</h2>";
    $stmt = $db->prepare("SELECT id, code, is_active, start_date, end_date FROM salon_offers WHERE salon_id = ?");
    $stmt->execute([$salonId]);
    $all = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1'><tr><th>ID</th><th>Code</th><th>Active</th><th>Start</th><th>End</th></tr>";
    foreach ($all as $offer) {
        echo "<tr>";
        foreach ($offer as $k => $v)
            echo "<td>$v</td>";
        echo "</tr>";
    }
    echo "</table>";

}
catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
