<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

$db = Database::getInstance()->getConnection();
$stmt = $db->query("DESCRIBE users");
echo "Users table columns:\n";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  {$row['Field']} ({$row['Type']})\n";
}
