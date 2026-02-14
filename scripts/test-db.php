<?php
// Test database connection and auth setup
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Database Connection Test ===\n\n";

try {
    $db = new PDO('mysql:host=localhost;dbname=salon_booking;charset=utf8mb4', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Database connection: SUCCESS\n";
    echo "✓ Database: salon_booking\n\n";

    // Check if tables exist
    echo "=== Checking Tables ===\n";
    $tables = ['users', 'profiles', 'salons', 'user_roles', 'services', 'bookings'];
    foreach ($tables as $table) {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            $count = $db->query("SELECT COUNT(*) FROM $table")->fetchColumn();
            echo "✓ Table '$table' exists ($count rows)\n";
        } else {
            echo "✗ Table '$table' MISSING\n";
        }
    }

    echo "\n=== Sample User Data ===\n";
    $stmt = $db->query("SELECT id, email, created_at FROM users LIMIT 3");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($users) > 0) {
        foreach ($users as $user) {
            echo "- User: {$user['email']} (ID: {$user['id']})\n";
        }
    } else {
        echo "No users found in database\n";
    }

} catch (PDOException $e) {
    echo "✗ Database connection: FAILED\n";
    echo "Error: " . $e->getMessage() . "\n";
}
