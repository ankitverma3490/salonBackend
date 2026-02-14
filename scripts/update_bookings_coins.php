<?php
require_once __DIR__ . '/../config.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";port=" . DB_PORT . ";charset=" . DB_CHARSET;
    $db = new PDO($dsn, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Updating Bookings table for coin support..." . PHP_EOL;

    // 1. Add coins_used column to bookings table
    try {
        $db->exec("ALTER TABLE bookings ADD COLUMN coins_used DECIMAL(15,2) DEFAULT 0.00 AFTER price_paid");
        echo "Added coins_used column." . PHP_EOL;
    } catch (PDOException $e) {
        echo "coins_used column already exists or error: " . $e->getMessage() . PHP_EOL;
    }

    // 2. Add coin_currency_value column (to record what the coin was worth at time of booking)
    try {
        $db->exec("ALTER TABLE bookings ADD COLUMN coin_currency_value DECIMAL(10,4) DEFAULT 1.00 AFTER coins_used");
        echo "Added coin_currency_value column." . PHP_EOL;
    } catch (PDOException $e) {
        echo "coin_currency_value column already exists or error: " . $e->getMessage() . PHP_EOL;
    }

    echo "Bookings table update completed." . PHP_EOL;

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
