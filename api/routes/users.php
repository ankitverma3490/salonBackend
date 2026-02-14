<?php
// User/Profile routes

// GET /api/users/me - Get current user profile
if ($method === 'GET' && $uriParts[1] === 'me') {
    $userData = Auth::getUserFromToken();
    if (!$userData) {
        sendResponse(['error' => 'Unauthorized'], 401);
    }

    $stmt = $db->prepare("
        SELECT u.id, u.email, u.email_verified, p.full_name, p.phone, p.avatar_url, p.avatar_public_id, p.user_type
        FROM users u
        LEFT JOIN profiles p ON u.id = p.user_id
        WHERE u.id = ?
    ");
    $stmt->execute([$userData['user_id']]);
    $user = $stmt->fetch();

    sendResponse(['user' => $user]);
}

// PUT /api/users/me - Update current user profile
if ($method === 'PUT' && $uriParts[1] === 'me') {
    $userData = Auth::getUserFromToken();
    if (!$userData) {
        sendResponse(['error' => 'Unauthorized'], 401);
    }

    $data = getRequestBody();

    $stmt = $db->prepare("
        UPDATE profiles SET
            full_name = ?, phone = ?, address = ?, avatar_url = ?, avatar_public_id = ?
        WHERE user_id = ?
    ");
    $stmt->execute([
        $data['full_name'] ?? null,
        $data['phone'] ?? null,
        $data['address'] ?? null,
        $data['avatar_url'] ?? null,
        $data['avatar_public_id'] ?? null,
        $userData['user_id']
    ]);

    $stmt = $db->prepare("
        SELECT u.id, u.email, p.full_name, p.phone, p.avatar_url, p.avatar_public_id, p.user_type
        FROM users u
        LEFT JOIN profiles p ON u.id = p.user_id
        WHERE u.id = ?
    ");
    $stmt->execute([$userData['user_id']]);
    $user = $stmt->fetch();

    sendResponse(['user' => $user]);
}

// GET /api/users/roles - Get user roles
if ($method === 'GET' && $uriParts[1] === 'roles') {
    $userId = $_GET['user_id'] ?? null;
    $salonId = $_GET['salon_id'] ?? null;

    $query = "SELECT * FROM user_roles WHERE 1=1";
    $params = [];

    if ($userId) {
        $query .= " AND user_id = ?";
        $params[] = $userId;
    }

    if ($salonId) {
        $query .= " AND salon_id = ?";
        $params[] = $salonId;
    }

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $roles = $stmt->fetchAll();

    sendResponse(['roles' => $roles]);
}

// GET /api/profiles/:userId - Get user profile by ID
if ($method === 'GET' && count($uriParts) === 2) {
    $userId = $uriParts[1];

    $stmt = $db->prepare("
        SELECT u.id, u.email, p.full_name, p.phone, p.avatar_url
        FROM users u
        LEFT JOIN profiles p ON u.id = p.user_id
        WHERE u.id = ?
    ");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        sendResponse(['error' => 'User not found'], 404);
    }

    sendResponse(['user' => $user]);
}

// PUT /api/users/:userId - Update user profile by ID (Staff/Owner capability)
if ($method === 'PUT' && count($uriParts) === 2 && $uriParts[1] !== 'me') {
    $userData = Auth::getUserFromToken();
    if (!$userData) {
        sendResponse(['error' => 'Unauthorized'], 401);
    }

    $targetUserId = $uriParts[1];
    $data = getRequestBody();
    $salonId = $data['salon_id'] ?? null;

    if (!$salonId) {
        sendResponse(['error' => 'Salon ID is required to verify permissions'], 400);
    }

    // Verify requesting user is staff/owner/admin at the specified salon
    $stmt = $db->prepare("SELECT role FROM user_roles WHERE user_id = ? AND salon_id = ?");
    $stmt->execute([$userData['user_id'], $salonId]);
    $role = $stmt->fetch();

    if (!$role) {
        sendResponse(['error' => 'Forbidden: You do not have permission in this salon'], 403);
    }

    // Update the profile
    $stmt = $db->prepare("
        UPDATE profiles SET
            full_name = ?, phone = ?, address = ?, avatar_url = ?, avatar_public_id = ?
        WHERE user_id = ?
    ");
    $stmt->execute([
        $data['full_name'] ?? null,
        $data['phone'] ?? null,
        $data['address'] ?? null,
        $data['avatar_url'] ?? null,
        $data['avatar_public_id'] ?? null,
        $targetUserId
    ]);

    sendResponse(['success' => true, 'message' => 'User profile updated successfully']);
}

sendResponse(['error' => 'User route not found'], 404);
