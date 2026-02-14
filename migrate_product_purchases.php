<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Auth.php';

try {
    $db = Database::getInstance()->getConnection();

    $sql = "CREATE TABLE IF NOT EXISTS customer_product_purchases (
        id VARCHAR(36) PRIMARY KEY,
        user_id VARCHAR(36) NOT NULL,
        salon_id VARCHAR(36) NOT NULL,
        product_name VARCHAR(255) NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        purchase_date DATE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (salon_id) REFERENCES salons(id) ON DELETE CASCADE,
        INDEX idx_user_salon (user_id, salon_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $db->exec($sql);
    echo "Table 'customer_product_purchases' created successfully.\n";

    // Seed some data for the first user if they exist
    $stmt = $db->query("SELECT id FROM users LIMIT 1");
    $user = $stmt->fetch();
    $stmt = $db->query("SELECT id FROM salons LIMIT 1");
    $salon = $stmt->fetch();

    if ($user && $salon) {
        $check = $db->prepare("SELECT COUNT(*) FROM customer_product_purchases WHERE user_id = ? AND salon_id = ?");
        $check->execute([$user['id'], $salon['id']]);
        if ($check->fetchColumn() == 0) {
            $stmt = $db->prepare("INSERT INTO customer_product_purchases (id, user_id, salon_id, product_name, price, purchase_date) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([Auth::generateUuid(), $user['id'], $salon['id'], 'Post-Care Serum', 45.00, date('Y-m-d')]);
            $stmt->execute([Auth::generateUuid(), $user['id'], $salon['id'], 'Daily Cleanser', 30.00, date('Y-m-d', strtotime('-1 day'))]);
            echo "Seeded initial product purchase data.\n";
        }
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
