<?php
// Reminders and Follow-ups routes

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$uriParts = explode('/', trim(parse_url($uri, PHP_URL_PATH), '/'));

// Skip 'backend', 'api' to get to the route
if (isset($uriParts[0]) && $uriParts[0] === 'backend')
    array_shift($uriParts);
if (isset($uriParts[0]) && $uriParts[0] === 'api')
    array_shift($uriParts);

// Check if it's the 'reminders' route
if (!isset($uriParts[0]) || $uriParts[0] !== 'reminders') {
    return; // Pass through to other routers
}

$userData = Auth::getUserFromToken();
if (!$userData) {
    sendResponse(['error' => 'Unauthorized'], 401);
}

$userId = $userData['user_id'];
$db = Database::getInstance()->getConnection();

// POST /api/reminders - Create a new reminder
if ($method === 'POST' && count($uriParts) === 1) {
    $data = getRequestBody();
    if (!$data)
        sendResponse(['error' => 'Missing data'], 400);

    $targetUserId = $data['user_id'] ?? null; // Customer ID
    $salonId = $data['salon_id'] ?? null;
    $bookingId = $data['booking_id'] ?? null;
    $title = $data['title'] ?? 'Follow-up Appointment';
    $message = $data['message'] ?? 'We hope you enjoyed your recent visit! It is time for your next session.';
    $scheduledAt = $data['scheduled_at'] ?? null; // YYYY-MM-DD HH:MM:SS
    $reminderType = $data['reminder_type'] ?? 'manual';

    if (!$targetUserId || !$salonId || !$scheduledAt) {
        sendResponse(['error' => 'Customer ID, Salon ID, and Scheduled Time are required'], 400);
    }

    // Verify user has permission for this salon
    $salonAccess = protectRoute(['owner', 'manager', 'staff'], 'manage_bookings', $salonId);

    $reminderId = Auth::generateUuid();
    $stmt = $db->prepare("INSERT INTO reminders (id, user_id, salon_id, booking_id, title, message, scheduled_at, reminder_type, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
    $success = $stmt->execute([$reminderId, $targetUserId, $salonId, $bookingId, $title, $message, $scheduledAt, $reminderType]);

    if (!$success) {
        sendResponse(['error' => 'Failed to create reminder'], 500);
    }

    sendResponse(['success' => true, 'reminder_id' => $reminderId]);
}

// GET /api/reminders - Get reminders (filtered)
if ($method === 'GET' && count($uriParts) === 1) {
    $salonId = $_GET['salon_id'] ?? null;
    $targetUserId = $_GET['user_id'] ?? null;

    if ($salonId) {
        protectRoute(['owner', 'manager', 'staff'], 'manage_bookings', $salonId);
        $stmt = $db->prepare("SELECT r.*, p.full_name as customer_name, p.phone as customer_phone FROM reminders r JOIN profiles p ON r.user_id = p.user_id WHERE r.salon_id = ? ORDER BY r.scheduled_at ASC");
        $stmt->execute([$salonId]);
    } else {
        $stmt = $db->prepare("SELECT r.*, s.name as salon_name FROM reminders r JOIN salons s ON r.salon_id = s.id WHERE r.user_id = ? ORDER BY r.scheduled_at ASC");
        $stmt->execute([$userId]);
    }

    $reminders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    sendResponse(['reminders' => $reminders]);
}

// DELETE /api/reminders/:id - Cancel reminder
if ($method === 'DELETE' && count($uriParts) === 2) {
    $reminderId = $uriParts[1];

    // Get reminder to check permission
    $stmt = $db->prepare("SELECT * FROM reminders WHERE id = ?");
    $stmt->execute([$reminderId]);
    $reminder = $stmt->fetch();

    if (!$reminder)
        sendResponse(['error' => 'Reminder not found'], 404);

    protectRoute(['owner', 'manager', 'staff'], 'manage_bookings', $reminder['salon_id']);

    $stmt = $db->prepare("UPDATE reminders SET status = 'cancelled' WHERE id = ?");
    $stmt->execute([$reminderId]);

    sendResponse(['success' => true]);
}

sendResponse(['error' => 'Reminder route not found'], 404);
