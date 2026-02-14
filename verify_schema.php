<?php
/**
 * Verify All Schema Fixes
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();

    echo "=== Schema Verification ===\n\n";

    $criticalColumns = [
        'salons' => ['price', 'cover_image_url', 'category', 'target_audience', 'rating'],
        'services' => ['price', 'duration_minutes', 'category'],
        'bookings' => ['price_paid', 'discount_amount', 'coins_used'],
    ];

    $allGood = true;

    foreach ($criticalColumns as $table => $columns) {
        echo "{$table} table:\n";
        $stmt = $db->query("DESCRIBE {$table}");
        $existingCols = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($columns as $col) {
            if (in_array($col, $existingCols)) {
                echo "  ✓ {$col}\n";
            }
            else {
                echo "  ✗ {$col} MISSING!\n";
                $allGood = false;
            }
        }
        echo "\n";
    }

    if ($allGood) {
        echo "✅ All critical columns exist!\n";
        echo "\nDatabase is ready for use.\n";
    }
    else {
        echo "❌ Some columns are still missing!\n";
        exit(1);
    }


}
catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
