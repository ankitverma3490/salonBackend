<?php
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "Connected. Updating salons...\n";

    $stmt = $db->prepare("UPDATE salons SET approval_status = 'approved'");
    $stmt->execute();

    echo "Updated " . $stmt->rowCount() . " salons to 'approved'.\n";

    $stmt = $db->query("SELECT id, name, approval_status FROM salons");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Current Salon Status:\n";
    foreach ($rows as $row) {
        echo " - " . $row['name'] . ": " . $row['approval_status'] . "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
