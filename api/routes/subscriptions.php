<?php
// Subscription routes

// GET /api/subscriptions/plans - Get all subscription plans
if ($method === 'GET' && $uriParts[1] === 'plans') {
    $stmt = $db->prepare("
        SELECT * FROM subscription_plans
        WHERE is_active = 1
        ORDER BY sort_order, price_monthly
    ");
    $stmt->execute();
    $plans = $stmt->fetchAll();

    sendResponse(['plans' => $plans]);
}

// GET /api/subscriptions/addons - Get all subscription add-ons
if ($method === 'GET' && $uriParts[1] === 'addons') {
    $stmt = $db->prepare("
        SELECT * FROM subscription_addons
        WHERE is_active = 1
        ORDER BY price_monthly ASC
    ");
    $stmt->execute();
    $addons = $stmt->fetchAll();

    sendResponse(['addons' => $addons]);
}

// GET /api/subscriptions/my - Get user's salon subscriptions
if ($method === 'GET' && $uriParts[1] === 'my') {
    $userData = Auth::getUserFromToken();
    if (!$userData) {
        sendResponse(['error' => 'Unauthorized'], 401);
    }

    $salonId = $_GET['salon_id'] ?? null;
    if (!$salonId) {
        sendResponse(['error' => 'salon_id is required'], 400);
    }

    // Check if user has access to salon (owner or manager)
    $stmt = $db->prepare("SELECT id FROM user_roles WHERE user_id = ? AND salon_id = ? AND role IN ('owner', 'manager')");
    $stmt->execute([$userData['user_id'], $salonId]);
    if (!$stmt->fetch()) {
        sendResponse(['error' => 'Forbidden'], 403);
    }

    $stmt = $db->prepare("
        SELECT ss.*, sp.name as plan_name, sp.description as plan_description, 
               sp.max_staff, sp.max_services, sp.features
        FROM salon_subscriptions ss
        INNER JOIN subscription_plans sp ON ss.plan_id = sp.id
        WHERE ss.salon_id = ?
        ORDER BY ss.created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$salonId]);
    $subscription = $stmt->fetch();

    if ($subscription) {
        // Calculate Usage
        $stmt = $db->prepare("SELECT COUNT(*) FROM staff_profiles WHERE salon_id = ? AND is_active = 1");
        $stmt->execute([$salonId]);
        $subscription['current_staff_count'] = $stmt->fetchColumn();

        $stmt = $db->prepare("SELECT COUNT(*) FROM services WHERE salon_id = ? AND is_active = 1");
        $stmt->execute([$salonId]);
        $subscription['current_service_count'] = $stmt->fetchColumn();

        // Parse features
        $subscription['features'] = json_decode($subscription['features'] ?? '[]');

        // Check computed status
        $isActive = $subscription['status'] === 'active';
        if ($subscription['end_date'] && strtotime($subscription['end_date']) < time()) {
            $isActive = false;
            $subscription['status'] = 'expired';
        }
        $subscription['is_valid'] = $isActive;
    }

    sendResponse(['subscription' => $subscription]);
}

sendResponse(['error' => 'Subscription route not found'], 404);