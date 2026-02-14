<?php
/**
 * 📨 MESSAGES API ROUTE
 */

$userData = Auth::getUserFromToken();
if (!$userData) {
    sendResponse(['error' => 'Authentication required'], 401);
}

$userId = $userData['user_id'];

// 1. ==========================================
// 🚀 GET /api/messages
// ==========================================
if ($method === 'GET' && (!isset($uriParts[1]) || $uriParts[1] === '')) {
    $salonId = $_GET['salon_id'] ?? null;

    // Check user's role in this salon if provided
    $userRole = 'staff'; // Default

    // Check if super admin first
    $stmt = $db->prepare("SELECT 1 FROM platform_admins WHERE user_id = ? AND is_active = 1");
    $stmt->execute([$userId]);
    if ($stmt->fetch()) {
        $userRole = 'super_admin';
    }
    else if ($salonId) {
        $stmt = $db->prepare("SELECT role FROM user_roles WHERE user_id = ? AND salon_id = ?");
        $stmt->execute([$userId, $salonId]);
        $roleInfo = $stmt->fetch();
        if ($roleInfo) {
            $userRole = $roleInfo['role'];
        }
    }

    // Owners and Super Admins can see all messages for the salon
    // Staff can only see messages they sent or received
    // Owners and Super Admins can see all messages for the salon
    // Staff can only see messages they sent or received
    if ($userRole === 'owner' || $userRole === 'super_admin') {
        $query = "
            SELECT m.id, m.sender_id, m.receiver_id, m.salon_id, m.subject, m.content, m.is_read, m.recipient_type, m.created_at,
                   COALESCE(p_sender.full_name, 'Unknown Sender') as sender_name,
                   COALESCE(p_receiver.full_name, 'Unknown Receiver') as receiver_name
            FROM messages m
            LEFT JOIN profiles p_sender ON m.sender_id = p_sender.user_id
            LEFT JOIN profiles p_receiver ON m.receiver_id = p_receiver.user_id
            WHERE m.salon_id = ?
        ";
        $params = [$salonId];
    }
    else {
        $query = "
            SELECT m.id, m.sender_id, m.receiver_id, m.salon_id, m.subject, m.content, m.is_read, m.recipient_type, m.created_at,
                   COALESCE(p_sender.full_name, 'Unknown Sender') as sender_name,
                   COALESCE(p_receiver.full_name, 'Unknown Receiver') as receiver_name
            FROM messages m
            LEFT JOIN profiles p_sender ON m.sender_id = p_sender.user_id
            LEFT JOIN profiles p_receiver ON m.receiver_id = p_receiver.user_id
            WHERE (m.sender_id = ? OR m.receiver_id = ? OR (m.recipient_type = 'staff' AND m.receiver_id IS NULL AND m.salon_id = ?))
        ";
        $params = [$userId, $userId, $salonId];
    }

    $query .= " ORDER BY m.created_at DESC LIMIT 50";

    try {
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        sendResponse($messages);
    }
    catch (PDOException $e) {
        error_log("[Messages API Error] " . $e->getMessage());
        sendResponse(['error' => 'Failed to fetch messages'], 500);
    }
}

// 2. ==========================================
// 🚀 POST /api/messages
// ==========================================
if ($method === 'POST' && (!isset($uriParts[1]) || $uriParts[1] === '')) {
    $data = getRequestBody();

    if (empty($data['content'])) {
        sendResponse(['error' => 'Message content is required'], 400);
    }

    $id = Auth::generateUuid();
    $senderId = $userId;
    $receiverId = $data['receiver_id'] ?? null;
    $salonId = $data['salon_id'] ?? null;
    $subject = $data['subject'] ?? 'No Subject';
    $content = $data['content'];
    $recipientType = $data['recipient_type'] ?? 'staff';

    // If receiver_id is not provided, we might need to find it based on recipient_type
    if (!$receiverId && $salonId) {
        if ($recipientType === 'owner') {
            // Find salon owner
            $stmt = $db->prepare("SELECT user_id FROM user_roles WHERE salon_id = ? AND role = 'owner' LIMIT 1");
            $stmt->execute([$salonId]);
            $owner = $stmt->fetch();
            if ($owner) {
                $receiverId = $owner['user_id'];
            }
        }
        else if ($recipientType === 'super_admin') {
            // Find any active super admin
            $stmt = $db->query("SELECT user_id FROM platform_admins WHERE is_active = 1 LIMIT 1");
            $admin = $stmt->fetch();
            if ($admin) {
                $receiverId = $admin['user_id'];
            }
        }
    }

    try {
        $stmt = $db->prepare("
            INSERT INTO messages (id, sender_id, receiver_id, salon_id, subject, content, recipient_type)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $id,
            $senderId,
            $receiverId,
            $salonId,
            $subject,
            $content,
            $recipientType
        ]);

        sendResponse(['message' => 'Message sent successfully', 'id' => $id], 201);
    }
    catch (Exception $e) {
        sendResponse(['error' => 'Failed to save message: ' . $e->getMessage()], 500);
    }
}

// 3. ==========================================
// 🚀 PATCH /api/messages/:id/read
// ==========================================
if ($method === 'PATCH' && isset($uriParts[2]) && $uriParts[2] === 'read') {
    $messageId = $uriParts[1];

    $stmt = $db->prepare("UPDATE messages SET is_read = 1 WHERE id = ? AND receiver_id = ?");
    $stmt->execute([$messageId, $userId]);

    sendResponse(['message' => 'Message marked as read']);
}

// 4. ==========================================
// 🚀 DELETE /api/messages/:id
// ==========================================
if ($method === 'DELETE' && isset($uriParts[1]) && $uriParts[1] !== '' && !isset($uriParts[2])) {
    $messageId = $uriParts[1];

    $stmt = $db->prepare("DELETE FROM messages WHERE id = ? AND (sender_id = ? OR receiver_id = ?)");
    $stmt->execute([$messageId, $userId, $userId]);

    sendResponse(['message' => 'Message deleted']);
}

sendResponse(['error' => 'Route not found', 'uriParts' => $uriParts, 'method' => $method], 404);
