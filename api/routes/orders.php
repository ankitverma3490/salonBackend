<?php
// Orders Routes

// POST /api/orders - Create a new order
if ($method === 'POST' && count($uriParts) === 1) {
    $data = getRequestBody();

    // Basic validation
    if (!isset($data['total_amount']) || !isset($data['items'])) {
        sendResponse(['error' => 'Missing required fields (total_amount, items)'], 400);
    }

    $id = Auth::generateUuid();
    $userId = 'guest'; // Default to 'guest' if not logged in or explicitly handled
    $guestEmail = $data['email'] ?? null;
    $guestName = isset($data['firstName']) ? ($data['firstName'] . ' ' . ($data['lastName'] ?? '')) : null;

    // Check if user is logged in
    $user = Auth::getUserFromToken();
    if ($user) {
        $userId = $user['user_id'];
    }

    try {
        // Ensure guest_name isn't null if we want to store it (column allows NULL usually, but let's be safe)
        if (!$guestName && !$user) {
            // If completely anonymous and no name provided? 
            $guestName = 'Guest';
        }

        $stmt = $db->prepare("
            INSERT INTO platform_orders (id, user_id, guest_email, guest_name, total_amount, status, items, shipping_address, created_at)
            VALUES (?, ?, ?, ?, ?, 'placed', ?, ?, NOW())
        ");

        $stmt->execute([
            $id,
            $userId,
            $guestEmail,
            $guestName,
            $data['total_amount'],
            json_encode($data['items']),
            json_encode($data['shipping_address'] ?? null)
        ]);

        // Notify Admins
        if (isset($notifService)) {
            $customerDisplayName = $guestName ?: ($guestEmail ?: 'Guest');
            $notifService->notifyAdmins(
                "New Product Order",
                "New order #{$id} placed by {$customerDisplayName} for RM {$data['total_amount']}",
                'success',
                '/super-admin/orders'
            );
        }

        sendResponse(['message' => 'Order placed successfully', 'order_id' => $id]);
    } catch (PDOException $e) {
        error_log("Order creation failed: " . $e->getMessage());
        sendResponse(['error' => 'Failed to create order'], 500);
    }
}

// GET /api/orders/my - Get my orders
if ($method === 'GET' && count($uriParts) === 2 && $uriParts[1] === 'my') {
    $user = Auth::getUserFromToken();
    if (!$user) {
        sendResponse(['error' => 'Unauthorized'], 401);
    }

    try {
        $stmt = $db->prepare("SELECT * FROM platform_orders WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user['user_id']]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Decode JSON fields
        foreach ($orders as &$order) {
            $order['items'] = json_decode($order['items']);
            $order['shipping_address'] = json_decode($order['shipping_address']);
        }

        sendResponse(['orders' => $orders]);
    } catch (PDOException $e) {
        sendResponse(['error' => 'Failed to fetch orders: ' . $e->getMessage()], 500);
    }
}


