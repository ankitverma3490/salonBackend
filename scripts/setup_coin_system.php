<?php
require_once __DIR__ . '/../config.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";port=" . DB_PORT . ";charset=" . DB_CHARSET;
    $db = new PDO($dsn, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Starting Coin System Setup..." . PHP_EOL;

    // 1. Add coin_balance to users table
    try {
        $db->exec("ALTER TABLE users ADD COLUMN coin_balance DECIMAL(15,2) DEFAULT 0.00 AFTER password_hash");
        echo "Added coin_balance column to users table." . PHP_EOL;
    }
    catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "coin_balance column already exists." . PHP_EOL;
        }
        else {
            throw $e;
        }
    }

    // 2. Create coin_transactions table
    $db->exec("CREATE TABLE IF NOT EXISTS coin_transactions (
        id VARCHAR(36) PRIMARY KEY,
        user_id VARCHAR(36) NOT NULL,
        amount DECIMAL(15,2) NOT NULL,
        transaction_type ENUM('earned', 'spent', 'refunded', 'admin_adjustment') NOT NULL,
        description TEXT,
        reference_id VARCHAR(36),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "Created coin_transactions table." . PHP_EOL;

    // 3. Add coin_price to platform_settings
    $stmt = $db->prepare("SELECT id FROM platform_settings WHERE setting_key = 'coin_price'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $id = bin2hex(random_bytes(16)); // Simple UUID fallback
        $stmt = $db->prepare("INSERT INTO platform_settings (id, setting_key, setting_value) VALUES (?, 'coin_price', '1.00')");
        $stmt->execute([$id]);
        echo "Added coin_price setting to platform_settings." . PHP_EOL;
    }
    else {
        echo "coin_price setting already exists." . PHP_EOL;
    }

    echo "Coin System Setup Completed Successfully!" . PHP_EOL;

}
catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
