<?php
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "Successfully connected to the database: " . DB_NAME;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
