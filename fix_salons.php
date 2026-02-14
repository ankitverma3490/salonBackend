<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/plain');

require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "1. Connected to database successfully.\n";

    // 1. Approve all existing salons
    $stmt = $db->prepare("UPDATE salons SET approval_status = 'approved', is_active = 1");
    $stmt->execute();
    echo "2. Updated " . $stmt->rowCount() . " salons to 'approved' and 'active'.\n";

    // 2. Set auto-approve to true in settings
    $stmt = $db->prepare("UPDATE platform_settings SET setting_value = 'true' WHERE setting_key = 'auto_approve_salons'");
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        echo "3. Auto-approve salons setting turned ON.\n";
    } else {
        echo "3. Auto-approve setting was already ON or not found.\n";
    }

    // 3. List current salons
    $stmt = $db->query("SELECT name, approval_status, is_active FROM salons");
    $salons = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "\n--- Current Salons ---\n";
    if (count($salons) === 0) {
        echo "No salons found in database.\n";
    }
    foreach ($salons as $s) {
        echo "Name: {$s['name']} | Status: {$s['approval_status']} | Active: {$s['is_active']}\n";
    }

} catch (Exception $e) {
    echo "\nERROR: " . $e->getMessage() . "\n";
}
