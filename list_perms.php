<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT name FROM permissions");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['name'] . "\n";
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
