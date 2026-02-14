<?php
/**
 * Comprehensive Database Schema Fixer
 * Checks and adds all missing columns across all tables
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();

    echo "=== Comprehensive Schema Fix ===\n\n";

    $fixes = 0;
    $errors = 0;

    // Define all required columns for each table
    $requiredColumns = [
        'salons' => [
            'cover_image_url TEXT AFTER description',
            'target_audience VARCHAR(255) AFTER category',
        ],
        'services' => [
            'price DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER description',
            'duration_minutes INT NOT NULL DEFAULT 30 AFTER price',
        ],
        'bookings' => [
            'price_paid DECIMAL(10,2) NULL AFTER service_id',
            'discount_amount DECIMAL(10,2) DEFAULT 0 AFTER price_paid',
            'coupon_code VARCHAR(50) NULL AFTER discount_amount',
            'coins_used DECIMAL(15,2) DEFAULT 0.00 AFTER coupon_code',
            'coin_currency_value DECIMAL(10,4) DEFAULT 1.00 AFTER coins_used',
        ],
    ];

    foreach ($requiredColumns as $table => $columns) {
        echo "Checking {$table} table...\n";

        // Get existing columns
        $stmt = $db->query("DESCRIBE {$table}");
        $existingCols = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($columns as $columnDef) {
            // Extract column name from definition
            preg_match('/^(\w+)/', $columnDef, $matches);
            $colName = $matches[1];

            if (!in_array($colName, $existingCols)) {
                echo "  Adding {$colName}... ";
                try {
                    $db->exec("ALTER TABLE {$table} ADD COLUMN {$columnDef}");
                    echo "✓\n";
                    $fixes++;
                }
                catch (PDOException $e) {
                    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                        echo "⊘ (exists)\n";
                    }
                    else {
                        echo "✗ Error: " . substr($e->getMessage(), 0, 80) . "\n";
                        $errors++;
                    }
                }
            }
            else {
                echo "  {$colName} exists ✓\n";
            }
        }
        echo "\n";
    }

    echo "=== Summary ===\n";
    echo "Columns added: {$fixes}\n";
    echo "Errors: {$errors}\n\n";

    if ($fixes > 0) {
        echo "✓ Schema fixes applied!\n";
    }
    else {
        echo "✓ All columns already exist!\n";
    }


}
catch (Exception $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
    exit(1);
}
