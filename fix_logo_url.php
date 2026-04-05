<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Modify logo_url to LONGTEXT
    echo "Modifying logo_url column...\n";
    $db->exec("ALTER TABLE salons MODIFY logo_url LONGTEXT");
    
    // Might as well do cover_image_url just in case
    echo "Modifying cover_image_url column...\n";
    $db->exec("ALTER TABLE salons MODIFY cover_image_url LONGTEXT");

    echo "Successfully updated columns to LONGTEXT.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
