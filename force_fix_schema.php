<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();

    echo "--- Bookings Table Schema ---\n";
    $stmt = $db->query("DESCRIBE bookings");
    $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $hasFinalPrice = false;
    $hasTotalPrice = false;

    foreach ($fields as $field) {
        echo "- {$field['Field']} ({$field['Type']})\n";
        if ($field['Field'] === 'final_price')
            $hasFinalPrice = true;
        if ($field['Field'] === 'total_price')
            $hasTotalPrice = true;
    }

    if (!$hasFinalPrice) {
        echo "\nAdding final_price column...\n";
        if ($hasTotalPrice) {
            $db->exec("ALTER TABLE bookings ADD COLUMN final_price DECIMAL(10,2) AFTER total_price");
        }
        else {
            $db->exec("ALTER TABLE bookings ADD COLUMN final_price DECIMAL(10,2)");
        }
        echo "Successfully added final_price.\n";
    }
    else {
        echo "\nfinal_price already exists.\n";
    }

}
catch (Exception $e) {
    echo "\nERROR: " . $e->getMessage() . "\n";
}
