<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

$db = Database::getInstance()->getConnection();

echo "=== Notifications Table ===\n";
$stmt = $db->query("DESCRIBE notifications");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  {$row['Field']} ({$row['Type']})\n";
}

echo "\n=== Staff_Profiles Table ===\n";
$stmt = $db->query("DESCRIBE staff_profiles");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  {$row['Field']} ({$row['Type']})\n";
}
