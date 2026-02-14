<?php
require_once __DIR__ . '/backend/Database.php';

try {
    $db = Database::getInstance()->getConnection();

    echo "Connected to database: " . DB_NAME . "\n";

    $sql = "CREATE TABLE IF NOT EXISTS platform_products (
        id VARCHAR(36) PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        image_url TEXT,
        category VARCHAR(100),
        target_audience ENUM('salon', 'customer', 'both') DEFAULT 'both',
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $db->exec($sql);
    echo "Table platform_products verified/created successfully\n";

    // Check if it's empty and add some sample data if so
    $stmt = $db->query("SELECT COUNT(*) FROM platform_products");
    if ($stmt->fetchColumn() == 0) {
        echo "Adding sample products...\n";
        $samples = [
            [
                'id' => '11111111-1111-1111-1111-111111111111',
                'name' => 'Premium Lavender Essential Oil',
                'description' => 'Pure organic lavender oil for aromatherapy and skin soothing.',
                'price' => 25.00,
                'category' => 'Essential Oils',
                'target_audience' => 'both'
            ],
            [
                'id' => '22222222-2222-2222-2222-222222222222',
                'name' => 'Professional Hair Dryer Pro X',
                'description' => 'High-power professional dryer for salon use.',
                'price' => 120.00,
                'category' => 'Equipment',
                'target_audience' => 'salon'
            ],
            [
                'id' => '33333333-3333-3333-3333-333333333333',
                'name' => 'Vitamin C Brightening Serum',
                'description' => 'Concentrated formula for radiant skin.',
                'price' => 35.00,
                'category' => 'Skin Care',
                'target_audience' => 'customer'
            ]
        ];

        $insert = $db->prepare("INSERT INTO platform_products (id, name, description, price, category, target_audience) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($samples as $s) {
            $insert->execute([$s['id'], $s['name'], $s['description'], $s['price'], $s['category'], $s['target_audience']]);
        }
        echo "Sample products added.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
