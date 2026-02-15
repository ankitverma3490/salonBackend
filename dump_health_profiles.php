<?php
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();

    echo "=== DATA DUMP: customer_salon_profiles ===\n";
    $stmt = $db->query("SELECT * FROM customer_salon_profiles");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($results)) {
        echo "Table is empty.\n";
    }
    else {
        foreach ($results as $row) {
            echo "User: " . $row['user_id'] . " | Salon: " . $row['salon_id'] . " | Skin: " . $row['skin_type'] . " | Allergies: " . $row['allergy_records'] . "\n";
        }
    }

}
catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
