<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "MySQL Version: " . $db->query("SELECT VERSION()")->fetchColumn() . "\n";
}
catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
