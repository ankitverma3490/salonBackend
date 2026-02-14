<?php
// Notification routes

$userData = Auth::getUserFromToken();
if (!$userData) {
    sendResponse(['error' => 'Unauthorized'], 401);
}

$userId = $userData['user_id'];

// GET /api/notifications - Get user notifications
if ($method === 'GET' && count($uriParts) === 1) {
    $unreadOnly = isset($_GET['unread_only']) && $_GET['unread_only'] === '1';

    $query = "SELECT * FROM notifications WHERE user_id = ?";
    $params = [$userId];

    if ($unreadOnly) {
        $query .= " AND is_read = 0";
    }

    $query .= " ORDER BY created_at DESC LIMIT 50";

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendResponse(['notifications' => $notifications]);
}

// PUT /api/notifications/:id/read - Mark notification as read
if ($method === 'PUT' && count($uriParts) === 3 && $uriParts[2] === 'read') {
    $notifId = $uriParts[1];

    $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->execute([$notifId, $userId]);

    sendResponse(['success' => true]);
}

// PUT /api/notifications/read-all - Mark all as read
if ($method === 'PUT' && count($uriParts) === 2 && $uriParts[1] === 'read-all') {
    $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    $stmt->execute([$userId]);

    sendResponse(['success' => true]);
}

// DELETE /api/notifications/:id - Delete/Dismiss notification
if ($method === 'DELETE' && count($uriParts) === 2) {
    $notifId = $uriParts[1];

    $stmt = $db->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
    $stmt->execute([$notifId, $userId]);

    sendResponse(['success' => true]);
}

sendResponse(['error' => 'Notification route not found'], 404);
