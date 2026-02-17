<?php
// Loyalty API Routes

require_once __DIR__ . '/../../Services/LoyaltyService.php';
$loyaltyService = new LoyaltyService($db);

// GET /api/loyalty/settings?salon_id=X
if ($method === 'GET' && in_array('settings', $uriParts)) {
    $salonId = $_GET['salon_id'] ?? null;
    if (!$salonId) {
        $userData = Auth::getUserFromToken();
        if ($userData && isset($userData['salon_id'])) {
            $salonId = $userData['salon_id'];
        }
        else {
            sendResponse(['error' => 'salon_id required'], 400);
        }
    }

    $settings = $loyaltyService->getSettings($salonId);
    sendResponse(['settings' => $settings]);
}

// POST /api/loyalty/settings
if ($method === 'POST' && in_array('settings', $uriParts)) {
    $userData = protectRoute(['owner', 'manager'], 'manage_loyalty');
    $data = getRequestBody();
    $salonId = $data['salon_id'] ?? $userData['salon_id'];

    if ($loyaltyService->updateSettings($salonId, $data)) {
        sendResponse(['success' => true, 'Settings updated']);
    }
    else {
        sendResponse(['error' => 'Failed to update settings'], 500);
    }
}

// GET /api/loyalty/rewards?salon_id=X
if ($method === 'GET' && in_array('rewards', $uriParts)) {
    $salonId = $_GET['salon_id'] ?? null;
    $activeOnly = isset($_GET['active_only']) && $_GET['active_only'] === 'true';

    if (!$salonId)
        sendResponse(['error' => 'salon_id required'], 400);

    $rewards = $loyaltyService->getRewards($salonId, $activeOnly);
    sendResponse(['rewards' => $rewards]);
}

// POST /api/loyalty/rewards
if ($method === 'POST' && in_array('rewards', $uriParts)) {
    $userData = protectRoute(['owner', 'manager'], 'manage_loyalty');
    $data = getRequestBody();
    $salonId = $data['salon_id'] ?? $userData['salon_id'];

    if (empty($data['name']) || empty($data['points_required'])) {
        sendResponse(['error' => 'Name and points required'], 400);
    }

    $id = $loyaltyService->createReward($salonId, $data);
    sendResponse(['success' => true, 'id' => $id], 201);
}

// DELETE /api/loyalty/rewards/:id
if ($method === 'DELETE' && in_array('rewards', $uriParts) && !empty($uriParts[2])) {
    $userData = protectRoute(['owner', 'manager'], 'manage_loyalty');
    $rewardId = $uriParts[2];
    $salonId = $_GET['salon_id'] ?? $userData['salon_id']; // Usually owner has explicit salon context

    if ($loyaltyService->deleteReward($salonId, $rewardId)) {
        sendResponse(['success' => true]);
    }
    else {
        sendResponse(['error' => 'Failed to delete reward'], 500);
    }
}

// GET /api/loyalty/my-points?salon_id=X
if ($method === 'GET' && in_array('my-points', $uriParts)) {
    $userData = Auth::getUserFromToken();
    if (!$userData)
        sendResponse(['error' => 'Unauthorized'], 401);

    $salonId = $_GET['salon_id'] ?? null;
    if (!$salonId)
        sendResponse(['error' => 'salon_id required'], 400);

    $points = $loyaltyService->getCustomerPoints($salonId, $userData['user_id']);
    sendResponse(['points' => $points]);
}

// GET /api/loyalty/all-points
if ($method === 'GET' && in_array('all-points', $uriParts)) {
    $userData = Auth::getUserFromToken();
    if (!$userData) {
        sendResponse(['error' => 'Unauthorized'], 401);
    }
    sendResponse(['points' => $loyaltyService->getAllCustomerPoints($userData['user_id'])]);
}

// GET /api/loyalty/fix-my-points
if ($method === 'GET' && in_array('fix-my-points', $uriParts)) {
    $userData = Auth::getUserFromToken();
    if (!$userData) sendResponse(['error' => 'Unauthorized'], 401);

    require_once __DIR__ . '/../../Services/CoinService.php';
    $coinService = new CoinService($db);

    $stmt = $db->prepare("
        SELECT b.*, s.name as service_name, ser.price as service_price
        FROM bookings b
        JOIN services ser ON b.service_id = ser.id
        LEFT JOIN services s ON b.service_id = s.id
        WHERE b.user_id = ? AND b.status = 'completed'
    ");
    $stmt->execute([$userData['user_id']]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $loyaltyAdded = 0;
    $coinsAdded = 0;

    foreach ($bookings as $b) {
        $amount = (float)($b['price_paid'] ?? 0);
        if ($amount <= 0) $amount = (float)($b['service_price'] ?? 0);
        if ($amount <= 0) continue;

        // 1. Loyalty Points
        if (!$loyaltyService->hasTransactionForReference($b['id'])) {
            if ($loyaltyService->earnPoints($b['salon_id'], $userData['user_id'], $amount, $b['id'])) {
                $loyaltyAdded++;
            }
        }

        // 2. Platform Coins
        if (!$coinService->hasTransactionForReference($b['id'])) {
            $earningRate = (float) $coinService->getSetting('coin_earning_rate', 10);
            if ($earningRate > 0) {
                $coinsToEarn = ceil($amount / $earningRate);
                if ($coinsToEarn > 0) {
                    if ($coinService->adjustBalance(
                        $userData['user_id'],
                        $coinsToEarn,
                        'earned',
                        "Retroactive coins for: " . ($b['service_name'] ?? $b['id']),
                        $b['id']
                    )) {
                        $coinsAdded++;
                    }
                }
            }
        }
    }

    sendResponse([
        'success' => true,
        'message' => "Scan complete.",
        'details' => [
            'bookings_scanned' => count($bookings),
            'loyalty_awards_fixed' => $loyaltyAdded,
            'coin_awards_fixed' => $coinsAdded
        ]
    ]);
}

// POST /api/loyalty/redeem
if ($method === 'POST' && in_array('redeem', $uriParts)) {
    $userData = Auth::getUserFromToken();
    if (!$userData)
        sendResponse(['error' => 'Unauthorized'], 401);

    $data = getRequestBody();
    $salonId = $data['salon_id'] ?? null;
    $rewardId = $data['reward_id'] ?? null;

    if (!$salonId || !$rewardId)
        sendResponse(['error' => 'Missing fields'], 400);

    $result = $loyaltyService->redeemPoints($salonId, $userData['user_id'], $rewardId);

    if (isset($result['error'])) {
        sendResponse($result, 400);
    }
    else {
        sendResponse($result);
    }
}

sendResponse(['error' => 'Loyalty route not found'], 404);
