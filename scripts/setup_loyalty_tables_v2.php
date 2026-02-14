<?php
require_once __DIR__ . '/backend/config.php';
require_once __DIR__ . '/backend/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "Database connection successful.\n";

    // 1. Loyalty Programs Table
    echo "Creating loyalty_programs...\n";
    $sql = "CREATE TABLE IF NOT EXISTS loyalty_programs (
        id VARCHAR(36) PRIMARY KEY,
        salon_id VARCHAR(36) NOT NULL,
        program_name VARCHAR(255) DEFAULT 'Loyalty Program',
        is_active TINYINT(1) DEFAULT 0,
        points_per_currency_unit DECIMAL(10, 2) DEFAULT 1.00, 
        min_points_redemption INT DEFAULT 100,
        signup_bonus_points INT DEFAULT 0,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_salon (salon_id),
        FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE
    )"; // Removed engine/charset to use DB defaults
    $db->exec($sql);
    echo "Table 'loyalty_programs' checked.\n";

    // 2. Loyalty Rewards Table
    echo "Creating loyalty_rewards...\n";
    $sql = "CREATE TABLE IF NOT EXISTS loyalty_rewards (
        id VARCHAR(36) PRIMARY KEY,
        salon_id VARCHAR(36) NOT NULL,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        points_required INT NOT NULL,
        discount_amount DECIMAL(10, 2) DEFAULT 0.00,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE
    )";
    $db->exec($sql);
    echo "Table 'loyalty_rewards' checked.\n";

    // 3. Loyalty Transactions
    echo "Creating loyalty_transactions...\n";
    $sql = "CREATE TABLE IF NOT EXISTS loyalty_transactions (
        id VARCHAR(36) PRIMARY KEY,
        salon_id VARCHAR(36) NOT NULL,
        user_id VARCHAR(36) NOT NULL,
        points INT NOT NULL,
        transaction_type ENUM('earned', 'redeemed', 'adjusted', 'bonus', 'refunded') NOT NULL,
        reference_id VARCHAR(36),
        description VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $db->exec($sql);
    echo "Table 'loyalty_transactions' checked.\n";

    // 4. Add loyalty_points column
    try {
        $db->exec("ALTER TABLE customer_salon_profiles ADD COLUMN loyalty_points INT DEFAULT 0 AFTER allergy_records");
        echo "Column 'loyalty_points' added.\n";
    } catch (Exception $e) {
        // Ignore duplicate column errors
        if (strpos($e->getMessage(), "Duplicate column") === false && strpos($e->getMessage(), "already exists") === false) {
            throw $e;
        }
        echo "Column 'loyalty_points' likely exists.\n";
    }

} catch (Exception $e) {
    echo "Detailed Error: " . $e->getMessage() . "\n";
    // Check specific constraint
    $db->query("SHOW ENGINE INNODB STATUS")->fetchAll();
    // Usually only super user can see the details, but sometimes helpful
}
