<?php
// Reviews routes
require_once __DIR__ . '/../../Auth.php';

// GET /api/reviews?service_id=xxx OR ?salon_id=xxx
if ($method === 'GET' && count($uriParts) === 1) {
    if (isset($_GET['service_id'])) {
        $stmt = $db->prepare("
            SELECT r.*, p.full_name as user_name, p.avatar_url as user_avatar, s.name as service_name
            FROM booking_reviews r
            JOIN profiles p ON r.user_id = p.user_id
            JOIN bookings b ON r.booking_id = b.id
            JOIN services s ON b.service_id = s.id
            WHERE b.service_id = ?
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$_GET['service_id']]);
    }
    else if (isset($_GET['salon_id'])) {
        $stmt = $db->prepare("
            SELECT r.*, p.full_name as user_name, p.avatar_url as user_avatar, s.name as service_name
            FROM booking_reviews r
            JOIN profiles p ON r.user_id = p.user_id
            JOIN bookings b ON r.booking_id = b.id
            JOIN services s ON b.service_id = s.id
            WHERE b.salon_id = ?
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$_GET['salon_id']]);
    }
    else {
        // If no filter provided, return all 5-star reviews for the landing page
        $stmt = $db->prepare("
            SELECT r.*, p.full_name as user_name, p.avatar_url as user_avatar, s.name as service_name
            FROM booking_reviews r
            JOIN profiles p ON r.user_id = p.user_id
            JOIN bookings b ON r.booking_id = b.id
            JOIN services s ON b.service_id = s.id
            WHERE r.rating >= 5
            ORDER BY r.created_at DESC
            LIMIT 10
        ");
        $stmt->execute();
    }

    $reviews = $stmt->fetchAll();
    sendResponse(['reviews' => $reviews]);
}

// POST /api/reviews - Add a review
if ($method === 'POST' && count($uriParts) === 1) {
    $userData = Auth::getUserFromToken();
    if (!$userData) {
        sendResponse(['error' => 'Unauthorized'], 401);
    }

    $data = getRequestBody();
    $reviewId = Auth::generateUuid();

    try {
        $stmt = $db->prepare("
            INSERT INTO booking_reviews (id, user_id, booking_id, rating, comment)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $reviewId,
            $userData['user_id'],
            $data['booking_id'],
            $data['rating'],
            $data['comment'] ?? null
        ]);

        $stmt = $db->prepare("SELECT * FROM booking_reviews WHERE id = ?");
        $stmt->execute([$reviewId]);
        $review = $stmt->fetch();

        sendResponse(['review' => $review], 201);
    }
    catch (Exception $e) {
        sendResponse(['error' => 'Failed to add review: ' . $e->getMessage()], 500);
    }
}
