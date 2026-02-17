<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();

    // Get all coupons
    $stmt = $db->query("SELECT * FROM salon_offers");
    $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h1>Debug: All Coupons</h1>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Salon ID</th><th>Code</th><th>Type</th><th>Value</th><th>Start Date</th><th>End Date</th><th>Active</th><th>Max Usage</th><th>Usage Count</th></tr>";

    foreach ($offers as $offer) {
        echo "<tr>";
        echo "<td>{$offer['id']}</td>";
        echo "<td>{$offer['salon_id']}</td>";
        echo "<td>{$offer['code']}</td>";
        echo "<td>{$offer['discount_type']}</td>";
        echo "<td>{$offer['discount_value']}</td>";
        echo "<td>{$offer['start_date']}</td>";
        echo "<td>{$offer['end_date']}</td>";
        echo "<td>{$offer['is_active']}</td>";
        echo "<td>{$offer['max_usage']}</td>";
        echo "<td>{$offer['usage_count']}</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<h2>Server Time: " . date('Y-m-d H:i:s') . "</h2>";


}
catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
