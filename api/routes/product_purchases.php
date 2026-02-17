<?php
// Product Purchases routes

// GET /api/product_purchases - List purchases for a user in a salon
if ($method === 'GET' && count($uriParts) === 1) {
    $userData = Auth::getUserFromToken();
    if (!$userData) {
        sendResponse(['error' => 'Unauthorized'], 401);
    }

    $userId = $_GET['user_id'] ?? null;
    $salonId = $_GET['salon_id'] ?? null;

    if (!$userId || !$salonId) {
        sendResponse(['error' => 'User ID and Salon ID are required'], 400);
    }

    // Check permission (user themselves or salon staff)
    $hasAccess = ($userData['user_id'] === $userId);
    if (!$hasAccess) {
        $stmt = $db->prepare("SELECT id FROM user_roles WHERE user_id = ? AND salon_id = ?");
        $stmt->execute([$userData['user_id'], $salonId]);
        $hasAccess = (bool)$stmt->fetch();
    }

    if (!$hasAccess) {
        sendResponse(['error' => 'Forbidden'], 403);
    }

    $stmt = $db->prepare("SELECT * FROM customer_product_purchases WHERE user_id = ? AND salon_id = ? ORDER BY purchase_date DESC");
    $stmt->execute([$userId, $salonId]);
    $purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse(['purchases' => $purchases]);
}

// POST /api/product_purchases - Add a new purchase
if ($method === 'POST' && count($uriParts) === 1) {
    $userData = Auth::getUserFromToken();
    if (!$userData) {
        sendResponse(['error' => 'Unauthorized'], 401);
    }

    $data = getRequestBody();
    $userId = $data['user_id'] ?? null;
    $salonId = $data['salon_id'] ?? null;
    $productName = $data['product_name'] ?? null;
    $price = $data['price'] ?? null;
    $purchaseDate = $data['purchase_date'] ?? date('Y-m-d');

    if (!$userId || !$salonId || !$productName || $price === null) {
        sendResponse(['error' => 'All fields (user_id, salon_id, product_name, price) are required'], 400);
    }

    // Check permission (only salon staff can add purchases)
    $stmt = $db->prepare("SELECT id FROM user_roles WHERE user_id = ? AND salon_id = ?");
    $stmt->execute([$userData['user_id'], $salonId]);
    if (!$stmt->fetch()) {
        sendResponse(['error' => 'Forbidden'], 403);
    }

    $id = Auth::generateUuid();
    $stmt = $db->prepare("INSERT INTO customer_product_purchases (id, user_id, salon_id, product_name, price, purchase_date) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$id, $userId, $salonId, $productName, $price, $purchaseDate]);

    // Points & Coins Integration
    try {
        require_once __DIR__ . '/../../Services/LoyaltyService.php';
        require_once __DIR__ . '/../../Services/CoinService.php';
        require_once __DIR__ . '/../../Services/NotificationService.php';

        $loyaltyService = new LoyaltyService($db, new NotificationService($db));
        $coinService = new CoinService($db);

        // Earn loyalty points
        $loyaltyService->earnPoints($salonId, $userId, $price, $id, "Points earned from purchase: $productName");

        // Earn platform coins
        $coinService->earnCoins($userId, $price, "Coins earned from purchase: $productName at " . ($data['salon_name'] ?? 'Salon'), $id);
    }
    catch (Exception $e) {
        error_log("Failed to process points/coins for product purchase: " . $e->getMessage());
    }

    sendResponse(['success' => true, 'id' => $id]);
}

sendResponse(['error' => 'Product purchases route not found'], 404);
