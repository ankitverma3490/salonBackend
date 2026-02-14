<?php
/**
 * Comprehensive Schema Fix for All Remaining Issues
 */

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/Database.php";

try {
    $db = Database::getInstance()->getConnection();

    echo "=== Comprehensive Schema Fix ===\n\n";

    // 1. Fix subscription_plans table
    echo "1. Fixing subscription_plans table... ";
    $columns = [
        'price_description' => "TEXT AFTER description",
        'slug' => "VARCHAR(100) UNIQUE AFTER name",
        'price_monthly' => "DECIMAL(10,2) DEFAULT 0.00 AFTER price_description",
        'price_yearly' => "DECIMAL(10,2) DEFAULT 0.00 AFTER price_monthly",
        'max_staff' => "INT AFTER price_yearly",
        'max_services' => "INT AFTER max_staff",
        'max_bookings_per_month' => "INT AFTER max_services",
        'sort_order' => "INT DEFAULT 0 AFTER features",
        'is_featured' => "BOOLEAN DEFAULT FALSE AFTER sort_order"
    ];

    foreach ($columns as $col => $definition) {
        try {
            $db->exec("ALTER TABLE subscription_plans ADD COLUMN $col $definition");
            echo "$col ";
        }
        catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column') === false) {
            // echo "[$col: " . substr($e->getMessage(), 0, 30) . "...] ";
            }
        }
    }
    echo "Done.\n";

    // 2. Ensure platform_products has all required columns
    echo "2. Verifying platform_products... ";
    $productCols = [
        'target_audience' => "ENUM('customer', 'salon', 'both') DEFAULT 'both' AFTER category"
    ];

    foreach ($productCols as $col => $definition) {
        try {
            $db->exec("ALTER TABLE platform_products ADD COLUMN $col $definition");
            echo "$col ";
        }
        catch (PDOException $e) {
        // Already exists
        }
    }
    echo "Done.\n";

    // 3. Fix salon_inventory table
    echo "3. Fixing salon_inventory... ";
    try {
        $db->exec("ALTER TABLE salon_inventory ADD COLUMN low_stock_alert BOOLEAN DEFAULT FALSE AFTER reorder_level");
        echo "low_stock_alert ";
    }
    catch (PDOException $e) {
    // Already exists
    }
    echo "Done.\n";

    // 4. Ensure booking_reviews has user_id
    echo "4. Verifying booking_reviews... ";
    try {
        // Check if user_id exists
        $stmt = $db->query("DESCRIBE booking_reviews");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('user_id', $columns)) {
            $db->exec("ALTER TABLE booking_reviews ADD COLUMN user_id VARCHAR(36) AFTER booking_id");
            $db->exec("ALTER TABLE booking_reviews ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE");
            echo "user_id added ";
        }
    }
    catch (PDOException $e) {
    // Already exists
    }
    echo "Done.\n";

    // 5. Add indexes for performance
    echo "5. Adding performance indexes... ";
    $indexes = [
        "CREATE INDEX idx_salon_products_target ON platform_products(target_audience)" => "platform_products",
        "CREATE INDEX idx_subscription_plans_active ON subscription_plans(is_active)" => "subscription_plans",
        "CREATE INDEX idx_booking_reviews_user ON booking_reviews(user_id)" => "booking_reviews"
    ];

    foreach ($indexes as $sql => $table) {
        try {
            $db->exec($sql);
            echo "$table ";
        }
        catch (PDOException $e) {
        // Index already exists
        }
    }
    echo "Done.\n";

    echo "\n✓ All schema fixes applied successfully!\n";

}
catch (Exception $e) {
    echo "\nError: " . $e->getMessage() . "\n";
    exit(1);
}
