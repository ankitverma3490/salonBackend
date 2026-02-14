<?php
require_once __DIR__ . '/backend/config.php';
require_once __DIR__ . '/backend/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "Database connection successful.\n";

    // 1. Loyalty Programs Table (Settings per salon)
    $sql = "CREATE TABLE IF NOT EXISTS loyalty_programs (
        id VARCHAR(36) PRIMARY KEY,
        salon_id VARCHAR(36) NOT NULL,
        program_name VARCHAR(255) DEFAULT 'Loyalty Program',
        is_active TINYINT(1) DEFAULT 0,
        points_per_currency_unit DECIMAL(10, 2) DEFAULT 1.00, -- e.g., 1 point per $1
        min_points_redemption INT DEFAULT 100,
        signup_bonus_points INT DEFAULT 0,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_salon (salon_id),
        FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $db->exec($sql);
    echo "Table 'loyalty_programs' created/checked.\n";

    // 2. Loyalty Rewards Table (Redeemable items)
    $sql = "CREATE TABLE IF NOT EXISTS loyalty_rewards (
        id VARCHAR(36) PRIMARY KEY,
        salon_id VARCHAR(36) NOT NULL,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        points_required INT NOT NULL,
        discount_amount DECIMAL(10, 2) DEFAULT 0.00, -- If it's a monetary discount
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $db->exec($sql);
    echo "Table 'loyalty_rewards' created/checked.\n";

    // 3. Loyalty Transactions (History log)
    $sql = "CREATE TABLE IF NOT EXISTS loyalty_transactions (
        id VARCHAR(36) PRIMARY KEY,
        salon_id VARCHAR(36) NOT NULL,
        user_id VARCHAR(36) NOT NULL,
        points INT NOT NULL, -- Positive for earn, negative for spend
        transaction_type ENUM('earned', 'redeemed', 'adjusted', 'bonus', 'refunded') NOT NULL,
        reference_id VARCHAR(36), -- e.g., booking_id or reward_id
        description VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $db->exec($sql);
    echo "Table 'loyalty_transactions' created/checked.\n";

    // 4. Add loyalty_points column to customer_salon_profiles for faster access
    try {
        $db->exec("ALTER TABLE customer_salon_profiles ADD COLUMN loyalty_points INT DEFAULT 0 AFTER allergy_records");
        echo "Column 'loyalty_points' added to 'customer_salon_profiles'.\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), "Duplicate column name") !== false) {
            echo "Column 'loyalty_points' already exists in 'customer_salon_profiles'.\n";
        } else {
            echo "Error adding column: " . $e->getMessage() . "\n";
        }
    }

} catch (Exception $e) {
    echo "Critial Error: " . $e->getMessage() . "\n";
}
