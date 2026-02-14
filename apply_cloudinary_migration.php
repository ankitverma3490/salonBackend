<?php
/**
 * 🚀 MIGRATION: ADD CLOUDINARY FIELDS
 * Adds public_id columns to store Cloudinary identifiers for easy deletion/updates
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';

try {
    $db = Database::getInstance()->getConnection();

    $tables_and_columns = [
        'salons' => ['logo_public_id', 'cover_image_public_id'],
        'services' => ['image_public_id'],
        'profiles' => ['avatar_public_id'],
        'treatment_records' => ['before_photo_public_id', 'after_photo_public_id'],
        'platform_products' => ['image_public_id', 'image_2_public_id', 'image_3_public_id', 'image_4_public_id'],
        'salon_inventory' => ['image_public_id']
    ];

    foreach ($tables_and_columns as $table => $columns) {
        foreach ($columns as $column) {
            // Check if column exists
            $check = $db->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
            if (!$check->fetch()) {
                echo "Adding $column to $table...\n";
                $db->exec("ALTER TABLE `$table` ADD COLUMN `$column` VARCHAR(255) NULL");
            } else {
                echo "Column $column already exists in $table.\n";
            }
        }
    }

    echo "Migration completed successfully!\n";

} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
