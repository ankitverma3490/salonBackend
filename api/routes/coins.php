<?php
/**
 * 🪙 COIN SYSTEM ROUTES
 */

require_once __DIR__ . '/../../Services/CoinService.php';
$coinService = new CoinService($db);

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// --- Universal Check for User Auth ---
$userData = Auth::getUserFromToken();

// 1. Super Admin Settings
if ($action === 'admin-get-price') {
    protectRoute(['super_admin']);
    sendResponse(['price' => $coinService->getCoinPrice()]);
}

if ($action === 'admin-set-price' && $method === 'POST') {
    protectRoute(['super_admin']);
    $body = getRequestBody();
    $price = $body['price'] ?? 1.00;

    if ($coinService->setCoinPrice($price, $userData['id'])) {
        sendResponse(['success' => true, 'message' => 'Coin price updated']);
    } else {
        sendResponse(['error' => 'Failed to update coin price'], 500);
    }
}

// 2. User Balance and Transactions
if ($action === 'get-balance') {
    if (!$userData)
        sendResponse(['error' => 'Unauthorized'], 401);
    sendResponse([
        'balance' => $coinService->getBalance($userData['id']),
        'price' => $coinService->getCoinPrice(),
        'settings' => [
            'min_redemption' => (float) $coinService->getSetting('coin_min_redemption', 0),
            'max_discount_percent' => (float) $coinService->getSetting('coin_max_discount_percent', 100),
            'earning_rate' => (float) $coinService->getSetting('coin_earning_rate', 10),
        ]
    ]);
}

if ($action === 'get-transactions') {
    if (!$userData)
        sendResponse(['error' => 'Unauthorized'], 401);
    sendResponse($coinService->getTransactions($userData['id']));
}

// 3. Admin Adjustment (for testing or manual grants)
if ($action === 'admin-adjust' && $method === 'POST') {
    protectRoute(['super_admin']);
    $body = getRequestBody();
    $targetUserId = $body['user_id'] ?? null;
    $amount = $body['amount'] ?? 0;
    $type = $body['type'] ?? 'admin_adjustment';
    $description = $body['description'] ?? 'Admin manual adjustment';

    if (!$targetUserId || $amount == 0) {
        sendResponse(['error' => 'Invalid parameters'], 400);
    }

    if ($coinService->adjustBalance($targetUserId, $amount, $type, $description)) {
        sendResponse(['success' => true, 'message' => 'Balance adjusted']);
    } else {
        sendResponse(['error' => 'Adjustment failed'], 500);
    }
}

// Fallback
sendResponse(['error' => 'Action not found'], 404);
