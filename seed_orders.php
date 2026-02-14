<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Auth.php';

try {
    $db = Database::getInstance()->getConnection();

    // Check if any orders exist
    $stmt = $db->query("SELECT COUNT(*) FROM platform_orders");
    if ($stmt->fetchColumn() == 0) {
        $id = Auth::generateUuid();
        $items = json_encode([
            ['name' => 'Premium Shampoo', 'quantity' => 2, 'price' => 25.00],
            ['name' => 'Conditioner', 'quantity' => 1, 'price' => 20.00]
        ]);
        $address = json_encode([
            'street' => '123 Salon St',
            'city' => 'Beauty City',
            'state' => 'NY',
            'zip' => '10001'
        ]);

        $sql = "INSERT INTO platform_orders (id, guest_name, guest_email, items, shipping_address, total_amount, status) 
                VALUES (?, 'John Doe', 'john@example.com', ?, ?, 70.00, 'placed')";

        $stmt = $db->prepare($sql);
        $stmt->execute([$id, $items, $address]);

        echo "Seeded one test order.\n";
    } else {
        echo "Orders already exist.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
