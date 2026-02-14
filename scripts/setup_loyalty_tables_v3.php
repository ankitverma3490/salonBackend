<?php
require_once __DIR__ . '/backend/config.php';
require_once __DIR__ . '/backend/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "Database connection successful.\n";

    // DROP Tables to ensure clean state
    $db->exec("DROP TABLE IF EXISTS loyalty_transactions");
    $db->exec("DROP TABLE IF EXISTS loyalty_rewards");
    $db->exec("DROP TABLE IF EXISTS loyalty_programs");

    // 1. Loyalty Programs Table
    echo "Creating loyalty_programs...\n";
    $sql = "CREATE TABLE loyalty_programs (
        id VARCHAR(36) NOT NULL PRIMARY KEY,
        salon_id VARCHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
        program_name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Loyalty Program',
        is_active TINYINT(1) DEFAULT 0,
        points_per_currency_unit DECIMAL(10, 2) DEFAULT 1.00,
        min_points_redemption INT DEFAULT 100,
        signup_bonus_points INT DEFAULT 0,
        description TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_salon (salon_id),
        CONSTRAINT fk_loyalty_salon FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $db->exec($sql);
    echo "Table 'loyalty_programs' created.\n";

    // 2. Loyalty Rewards Table
    echo "Creating loyalty_rewards...\n";
    $sql = "CREATE TABLE loyalty_rewards (
        id VARCHAR(36) NOT NULL PRIMARY KEY,
        salon_id VARCHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
        name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
        description TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
        points_required INT NOT NULL,
        discount_amount DECIMAL(10, 2) DEFAULT 0.00,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY idx_salon (salon_id),
        CONSTRAINT fk_loyalty_rewards_salon FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $db->exec($sql);
    echo "Table 'loyalty_rewards' created.\n";

    // 3. Loyalty Transactions
    echo "Creating loyalty_transactions...\n";
    $sql = "CREATE TABLE loyalty_transactions (
        id VARCHAR(36) NOT NULL PRIMARY KEY,
        salon_id VARCHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
        user_id VARCHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
        points INT NOT NULL,
        transaction_type ENUM('earned', 'redeemed', 'adjusted', 'bonus', 'refunded') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
        reference_id VARCHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
        description VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY idx_salon_user (salon_id, user_id),
        CONSTRAINT fk_loyalty_trans_salon FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE,
        CONSTRAINT fk_loyalty_trans_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $db->exec($sql);
    echo "Table 'loyalty_transactions' created.\n";

    // 4. Add loyalty_points column (already done, but safe to retry/check)
    // Skipped to avoid noise, focusing on main tables

} catch (Exception $e) {
    echo "Detailed Error: " . $e->getMessage() . "\n";
}
