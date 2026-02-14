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
        } else {
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
    } else {
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
    } else {
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
    } else {
        sendResponse($result);
    }
}

sendResponse(['error' => 'Loyalty route not found'], 404);
