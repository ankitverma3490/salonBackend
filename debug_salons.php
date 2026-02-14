<?php
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT id, name, is_active, approval_status FROM salons");
    $salons = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Total salons: " . count($salons) . "\n";
    foreach ($salons as $salon) {
        echo "ID: " . $salon['id'] . " | Name: " . $salon['name'] . " | Active: " . $salon['is_active'] . " | Status: " . $salon['approval_status'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
