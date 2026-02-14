<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Auth.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";port=" . DB_PORT . ";charset=" . DB_CHARSET;
    $db = new PDO($dsn, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Adding Advanced Coin Settings..." . PHP_EOL;

    $settings = [
        'coin_earning_rate' => ['value' => '10.00', 'desc' => 'Currency units spent to earn 1 coin'],
        'coin_min_redemption' => ['value' => '10.00', 'desc' => 'Minimum coins required for a single redemption'],
        'coin_max_discount_percent' => ['value' => '50.00', 'desc' => 'Maximum percentage of service price that can be paid with coins'],
        'coin_signup_bonus' => ['value' => '0.00', 'desc' => 'Coins awarded to new clinical accounts upon registration']
    ];

    foreach ($settings as $key => $data) {
        $stmt = $db->prepare("SELECT id FROM platform_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        if (!$stmt->fetch()) {
            $id = bin2hex(random_bytes(16));
            $stmt = $db->prepare("INSERT INTO platform_settings (id, setting_key, setting_value, description) VALUES (?, ?, ?, ?)");
            $stmt->execute([$id, $key, $data['value'], $data['desc']]);
            echo "Added $key setting." . PHP_EOL;
        } else {
            echo "$key setting already exists." . PHP_EOL;
        }
    }

    echo "Advanced Coin Settings Setup Completed!" . PHP_EOL;

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
