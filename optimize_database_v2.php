<?php
/**
 * Database Optimization Script V2
 * Adds indexes to critical columns for improved query performance.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "=== Database Optimization V2 ===\n\n";

    $optimizations = [
        'bookings' => [
            'idx_bookings_status_date' => ['status', 'booking_date'],
            'idx_bookings_staff_date' => ['staff_id', 'booking_date']
        ],
        'staff_attendance' => [
            'idx_attendance_staff_checkin' => ['staff_id', 'check_in'],
            'idx_attendance_checkout' => ['check_out']
        ],
        'staff_leaves' => [
            'idx_leaves_staff_dates' => ['staff_id', 'start_date', 'end_date'],
            'idx_leaves_status' => ['status']
        ],
        'messages' => [
            'idx_messages_salon_read' => ['salon_id', 'is_read'],
            'idx_messages_receiver_read' => ['receiver_id', 'is_read']
        ],
        'salons' => [
            'idx_salons_active_status' => ['is_active', 'approval_status']
        ]
    ];

    foreach ($optimizations as $table => $indexes) {
        echo "Updating table: $table\n";

        // Get existing indexes
        $stmt = $db->query("SHOW INDEX FROM `$table`");
        $existing = $stmt->fetchAll(PDO::FETCH_COLUMN, 2);

        foreach ($indexes as $indexName => $columns) {
            if (in_array($indexName, $existing)) {
                echo "  - Index $indexName already exists. ✓\n";
                continue;
            }

            $colList = implode(', ', $columns);
            echo "  - Adding index $indexName ($colList)... ";
            try {
                $db->exec("ALTER TABLE `$table` ADD INDEX `$indexName` ($colList)");
                echo "✓\n";
            }
            catch (Exception $e) {
                echo "Failed: " . $e->getMessage() . "\n";
            }
        }
        echo "\n";
    }

    echo "=== Optimization Complete ===\n";

}
catch (Exception $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
}
