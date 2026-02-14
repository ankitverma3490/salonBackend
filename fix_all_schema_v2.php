<?php
/**
 * Comprehensive Schema Fix v2
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "=== Starting Comprehensive Schema Fix v2 ===\n\n";

    function addColumns($db, $tableName, $columns)
    {
        // Get existing columns
        $stmt = $db->query("DESCRIBE `{$tableName}`");
        $existingCols = $stmt->fetchAll(PDO::FETCH_COLUMN);

        echo "Table: {$tableName}\n";
        foreach ($columns as $colName => $definition) {
            if (!in_array($colName, $existingCols)) {
                echo "  Adding {$colName}... ";
                try {
                    $db->exec("ALTER TABLE `{$tableName}` ADD COLUMN {$colName} {$definition}");
                    echo "✓\n";
                }
                catch (PDOException $e) {
                    echo "✗ Error: " . $e->getMessage() . "\n";
                }
            }
            else {
                echo "  {$colName} already exists ✓\n";
            }
        }
        echo "\n";
    }

    // 1. subscription_plans
    addColumns($db, 'subscription_plans', [
        'slug' => "VARCHAR(255) NOT NULL UNIQUE AFTER name",
        'max_staff' => "INT DEFAULT 5 AFTER price",
        'max_services' => "INT DEFAULT 20 AFTER max_staff",
        'max_bookings_per_month' => "INT AFTER max_services",
        'is_featured' => "BOOLEAN DEFAULT FALSE AFTER is_active",
        'sort_order' => "INT DEFAULT 0 AFTER is_featured"
    ]);

    // 2. bookings
    addColumns($db, 'bookings', [
        'staff_id' => "VARCHAR(36) AFTER service_id",
        'notes' => "TEXT AFTER coupon_code"
    ]);

    // 3. services
    addColumns($db, 'services', [
        'image_public_id' => "VARCHAR(255) AFTER image_url",
        'is_featured' => "BOOLEAN DEFAULT FALSE AFTER is_active"
    ]);

    // 4. salons
    addColumns($db, 'salons', [
        'logo_public_id' => "VARCHAR(255) AFTER logo_url",
        'cover_image_public_id' => "VARCHAR(255) AFTER cover_image_url",
        'business_hours' => "JSON AFTER is_active",
        'notification_settings' => "JSON AFTER business_hours",
        'gst_number' => "VARCHAR(50) AFTER notification_settings",
        'upi_id' => "VARCHAR(100) AFTER gst_number",
        'bank_details' => "JSON AFTER upi_id",
        'approval_status' => "ENUM('pending', 'approved', 'rejected') DEFAULT 'pending' AFTER bank_details"
    ]);

    echo "=== Schema Fix Complete ===\n";

}
catch (Exception $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
    exit(1);
}
