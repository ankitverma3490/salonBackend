<?php
/**
 * Fix Services Table - Add Missing Columns
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();

    echo "=== Fixing Services Table ===\n\n";

    // Check current structure
    echo "Current services table structure:\n";
    $stmt = $db->query("DESCRIBE services");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($columns as $col) {
        echo "  - {$col}\n";
    }
    echo "\n";

    $updates = [];

    // Add price column if missing
    if (!in_array('price', $columns)) {
        echo "Adding price column... ";
        try {
            $db->exec("ALTER TABLE services ADD COLUMN price DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER description");
            echo "✓\n";
            $updates[] = 'price';
        }
        catch (PDOException $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
    }
    else {
        echo "price column already exists ✓\n";
    }

    // Add duration_minutes column if missing
    if (!in_array('duration_minutes', $columns)) {
        echo "Adding duration_minutes column... ";
        try {
            $db->exec("ALTER TABLE services ADD COLUMN duration_minutes INT NOT NULL DEFAULT 30 AFTER price");
            echo "✓\n";
            $updates[] = 'duration_minutes';
        }
        catch (PDOException $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
    }
    else {
        echo "duration_minutes column already exists ✓\n";
    }

    // Add category column if missing
    if (!in_array('category', $columns)) {
        echo "Adding category column... ";
        try {
            $db->exec("ALTER TABLE services ADD COLUMN category VARCHAR(100) AFTER duration_minutes");
            echo "✓\n";
            $updates[] = 'category';
        }
        catch (PDOException $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
    }
    else {
        echo "category column already exists ✓\n";
    }

    // Add image_url column if missing
    if (!in_array('image_url', $columns)) {
        echo "Adding image_url column... ";
        try {
            $db->exec("ALTER TABLE services ADD COLUMN image_url TEXT AFTER category");
            echo "✓\n";
            $updates[] = 'image_url';
        }
        catch (PDOException $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
    }
    else {
        echo "image_url column already exists ✓\n";
    }

    // Add is_active column if missing
    if (!in_array('is_active', $columns)) {
        echo "Adding is_active column... ";
        try {
            $db->exec("ALTER TABLE services ADD COLUMN is_active BOOLEAN DEFAULT TRUE AFTER image_url");
            echo "✓\n";
            $updates[] = 'is_active';
        }
        catch (PDOException $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
    }
    else {
        echo "is_active column already exists ✓\n";
    }

    echo "\n=== Updated Structure ===\n";
    $stmt = $db->query("DESCRIBE services");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  {$row['Field']} - {$row['Type']}\n";
    }

    echo "\n✓ Services table fixed!\n";

    if (!empty($updates)) {
        echo "\nAdded columns: " . implode(', ', $updates) . "\n";
    }


}
catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
