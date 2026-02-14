<?php
// Booking routes
require_once __DIR__ . '/../../Services/CoinService.php';
$coinService = new CoinService($db);

// GET /api/bookings - Get bookings (filtered by user or salon)
if ($method === 'GET' && count($uriParts) === 1) {
    $userData = Auth::getUserFromToken();
    if (!$userData) {
        sendResponse(['error' => 'Unauthorized'], 401);
    }

    $salonId = $_GET['salon_id'] ?? null;
    $userId = $_GET['user_id'] ?? $userData['user_id'];

    if ($salonId) {
        $userData = protectRoute(['owner', 'manager', 'staff'], 'view_bookings', $salonId);

        $date = $_GET['date'] ?? null;
        $status = $_GET['status'] ?? null;
        $staffId = $_GET['staff_id'] ?? null;

        $params = [$salonId];
        $whereSql = "WHERE b.salon_id = ?";

        if ($date) {
            $whereSql .= " AND b.booking_date = ?";
            $params[] = $date;
        }
        if ($status) {
            $whereSql .= " AND b.status = ?";
            $params[] = $status;
        }
        if ($staffId) {
            $whereSql .= " AND b.staff_id = ?";
            $params[] = $staffId;
        }

        $stmt = $db->prepare("
SELECT b.*, s.name as service_name, s.price as service_price, s.duration_minutes, s.category,
sal.name as salon_name, sal.address as salon_address, sal.city as salon_city,
u.email, p.full_name, p.phone,
sp.display_name as staff_name,
COALESCE(b.price_paid, s.price) as price
FROM bookings b
INNER JOIN services s ON b.service_id = s.id
INNER JOIN salons sal ON b.salon_id = sal.id
INNER JOIN users u ON b.user_id = u.id
LEFT JOIN profiles p ON u.id = p.user_id
LEFT JOIN staff_profiles sp ON b.staff_id = sp.id
$whereSql
ORDER BY b.booking_date DESC, b.booking_time DESC
");
        $stmt->execute($params);
    } else {
        // Get user's bookings
        $stmt = $db->prepare("
SELECT b.*, s.name as service_name, s.price, s.duration_minutes, s.category,
sal.name as salon_name, sal.address as salon_address, sal.city as salon_city, sal.phone as salon_phone
FROM bookings b
INNER JOIN services s ON b.service_id = s.id
INNER JOIN salons sal ON b.salon_id = sal.id
WHERE b.user_id = ?
ORDER BY b.booking_date DESC, b.booking_time DESC
");
        $stmt->execute([$userId]);
    }

    $bookings = $stmt->fetchAll();
    sendResponse(['bookings' => $bookings]);
}

// GET /api/bookings/:id - Get booking by ID
if ($method === 'GET' && count($uriParts) === 2) {
    $userData = Auth::getUserFromToken();
    if (!$userData) {
        sendResponse(['error' => 'Unauthorized'], 401);
    }

    $bookingId = $uriParts[1];
    $stmt = $db->prepare("
SELECT b.*, s.name as service_name, s.price, s.duration_minutes, s.category,
sal.name as salon_name, sal.address as salon_address, sal.city as salon_city, sal.phone as salon_phone,
u.email, p.full_name, p.phone
FROM bookings b
INNER JOIN services s ON b.service_id = s.id
INNER JOIN salons sal ON b.salon_id = sal.id
INNER JOIN users u ON b.user_id = u.id
LEFT JOIN profiles p ON u.id = p.user_id
WHERE b.id = ?
");
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch();

    if (!$booking) {
        sendResponse(['error' => 'Booking not found'], 404);
    }

    // Check if user has access
    $hasAccess = ($booking['user_id'] === $userData['user_id']);
    if (!$hasAccess) {
        $stmt = $db->prepare("SELECT id FROM user_roles WHERE user_id = ? AND salon_id = ?");
        $stmt->execute([$userData['user_id'], $booking['salon_id']]);
        $hasAccess = (bool) $stmt->fetch();
    }

    if (!$hasAccess) {
        sendResponse(['error' => 'Forbidden'], 403);
    }

    sendResponse(['booking' => $booking]);
}

// POST /api/bookings - Create new booking
if ($method === 'POST' && count($uriParts) === 1) {
    $userData = Auth::getUserFromToken();
    if (!$userData) {
        sendResponse(['error' => 'Unauthorized'], 401);
    }

    $data = getRequestBody();
    $bookingId = Auth::generateUuid();

    // 1. Intelligent Staff Availability Check
    $targetStaffId = $data['staff_id'] ?? null;
    $serviceId = $data['service_id'];
    $bookingDate = $data['booking_date'];
    $bookingTime = $data['booking_time'];

    if ($targetStaffId) {
        // Specific staff selected - check if they handle the service AND are free
        $bookingStatus = $data['status'] ?? 'confirmed';
        $stmt = $db->prepare("SELECT id FROM staff_services WHERE staff_id = ? AND service_id = ?");
        $stmt->execute([$targetStaffId, $serviceId]);
        if (!$stmt->fetch()) {
            sendResponse(['error' => 'The selected specialist does not handle this specific service.'], 400);
        }

        $stmt = $db->prepare("SELECT id FROM bookings WHERE staff_id = ? AND booking_date = ? AND booking_time = ? AND status !=
'cancelled'");
        $stmt->execute([$targetStaffId, $bookingDate, $bookingTime]);
        if ($stmt->fetch()) {
            sendResponse(['error' => 'The selected specialist is already occupied at this time slot.'], 409);
        }
    } else {
        // No staff selected - find ANY free staff that handles this service
        $stmt = $db->prepare("
SELECT s.id
FROM staff_profiles s
INNER JOIN staff_services ss ON s.id = ss.staff_id
WHERE ss.service_id = ? AND s.is_active = 1
AND NOT EXISTS (
SELECT 1 FROM bookings b
WHERE b.staff_id = s.id
AND b.booking_date = ?
AND b.booking_time = ?
AND b.status != 'cancelled'
)
LIMIT 1
");
        $stmt->execute([$serviceId, $bookingDate, $bookingTime]);
        $availableStaff = $stmt->fetch();

        if (!$availableStaff) {
            // Instead of failing, we allow the booking but mark it as pending assignment
            $targetStaffId = null;
            $bookingStatus = 'pending';
        } else {
            $targetStaffId = $availableStaff['id'];
            $bookingStatus = $data['status'] ?? 'confirmed';
        }
    }

    // 2. Coin Payment Integration
    $useCoins = $data['use_coins'] ?? false;
    $coinsToUse = 0;
    $coinValueInCurrency = 0;
    $coinPrice = $coinService->getCoinPrice();

    if ($useCoins) {
        $userBalance = $coinService->getBalance($userData['user_id']);
        $minRedemption = (float) $coinService->getSetting('coin_min_redemption', 0);
        $maxDiscountPercent = (float) $coinService->getSetting('coin_max_discount_percent', 100);

        if ($userBalance < $minRedemption) {
            sendResponse(['error' => "A minimum of {$minRedemption} coins is required for redemption."], 400);
        }

        if ($userBalance > 0) {
            // Get service price to know max useful coins
            $stmt = $db->prepare("SELECT price, name FROM services WHERE id = ?");
            $stmt->execute([$serviceId]);
            $service = $stmt->fetch();
            $basePrice = $service['price'] ?? 0;

            // Max coins calculation with constraints
            $maxAllowedDiscount = $basePrice * ($maxDiscountPercent / 100);
            $maxPossibleCoinValue = min($basePrice, $maxAllowedDiscount);
            $coinsNeeded = $maxPossibleCoinValue / $coinPrice;

            $coinsToUse = min($userBalance, $coinsNeeded);
            $coinValueInCurrency = $coinsToUse * $coinPrice;

            // Spend the coins
            $res = $coinService->spendCoins(
                $userData['user_id'],
                $coinsToUse,
                "Booking payment for service: " . ($service['name'] ?? $serviceId),
                $bookingId
            );

            if (isset($res['error'])) {
                sendResponse(['error' => $res['error']], 400);
            }
        }
    }

    $finalPricePaid = ($data['price_paid'] ?? 0) - $coinValueInCurrency;
    if ($finalPricePaid < 0)
        $finalPricePaid = 0;
    $stmt = $db->prepare("
    INSERT INTO bookings (id, user_id, salon_id, service_id, staff_id, price_paid, coins_used, coin_currency_value,
    discount_amount, coupon_code, booking_date, booking_time, notes, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $bookingId,
        $userData['user_id'],
        $data['salon_id'],
        $serviceId,
        $targetStaffId,
        $finalPricePaid,
        $coinsToUse,
        $coinPrice,
        $data['discount_amount'] ?? 0,
        $data['coupon_code'] ?? null,
        $bookingDate,
        $bookingTime,
        $data['notes'] ?? null,
        $bookingStatus
    ]);

    // Get booking with service name
    $stmt = $db->prepare("
    SELECT b.*, s.name as service_name, sal.name as salon_name
    FROM bookings b
    INNER JOIN services s ON b.service_id = s.id
    INNER JOIN salons sal ON b.salon_id = sal.id
    WHERE b.id = ?
    ");
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch();

    // Create notification for salon owner
    $stmt = $db->prepare("SELECT user_id FROM user_roles WHERE salon_id = ? AND role = 'owner'");
    $stmt->execute([$data['salon_id']]);
    $owner = $stmt->fetch();

    if ($owner) {
        $notifId = Auth::generateUuid();
        $stmt = $db->prepare("
    INSERT INTO notifications (id, user_id, salon_id, title, message, type, link)
    VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
        $stmt->execute([
            $notifId,
            $owner['user_id'],
            $data['salon_id'],
            $targetStaffId ? 'New Appointment' : 'Staff Assignment Required',
            $targetStaffId
            ? "New session booked for {$booking['service_name']} on " . date('M d', strtotime($booking['booking_date']))
            : "A new booking for {$booking['service_name']} needs a specialist assigned for " . date(
                'M d',
                strtotime($booking['booking_date'])
            ),
            'booking',
            '/dashboard/appointments'
        ]);
    }

    sendResponse(['booking' => $booking], 201);
}

// PUT /api/bookings/:id - Update booking status
if ($method === 'PUT' && count($uriParts) === 2) {
    $userData = Auth::getUserFromToken();
    if (!$userData) {
        sendResponse(['error' => 'Unauthorized'], 401);
    }

    $bookingId = $uriParts[1];
    $data = getRequestBody();

    // Get booking
    $stmt = $db->prepare("SELECT * FROM bookings WHERE id = ?");
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch();

    if (!$booking) {
        sendResponse(['error' => 'Booking not found'], 404);
    }

    // Check if user is the one who made the booking OR is salon staff
    $isOwner = ($booking['user_id'] === $userData['user_id']);

    if (!$isOwner) {
        $userData = protectRoute(['owner', 'manager', 'staff'], 'manage_bookings', $booking['salon_id']);
    }

    // Only allow customers to CANCEL, other status updates are for staff
    if ($isOwner && $data['status'] !== 'cancelled' && $userData['role'] === 'customer') {
        sendResponse(['error' => 'Customers can only cancel their appointments.'], 403);
    }

    $status = $data['status'] ?? $booking['status'];
    $staffId = $data['staff_id'] ?? $booking['staff_id'];

    $stmt = $db->prepare("UPDATE bookings SET status = ?, staff_id = ? WHERE id = ?");
    $success = $stmt->execute([$status, $staffId, $bookingId]);

    if (!$success) {
        sendResponse(['error' => 'Failed to update booking in database.'], 500);
    }

    // Loyalty & Coin Integration
    if ($status === 'completed' && $booking['status'] !== 'completed') {
        // 1. Loyalty Points
        require_once __DIR__ . '/../../Services/LoyaltyService.php';
        $loyaltyService = new LoyaltyService($db);

        // 2. Platform Coins
        require_once __DIR__ . '/../../Services/CoinService.php';
        $coinService = new CoinService($db);

        // Calculate amount paid (price_paid or service price)
        $amount = $booking['price_paid'] ?? $booking['price'] ?? 0;
        if ($amount > 0) {
            // Earn loyalty points
            $loyaltyService->earnPoints($booking['salon_id'], $booking['user_id'], $amount, $bookingId);

            // Earn platform coins (based on dynamic earning rate)
            $earningRate = (float) $coinService->getSetting('coin_earning_rate', 10);
            if ($earningRate > 0) {
                $coinsToEarn = ceil($amount / $earningRate);
                if ($coinsToEarn > 0) {
                    $coinService->adjustBalance(
                        $booking['user_id'],
                        $coinsToEarn,
                        'earned',
                        "Coins earned for booking: " . ($booking['service_name'] ?? $bookingId),
                        $bookingId
                    );
                }
            }
        }
    }

    // Notify owner if customer cancelled
    if ($status === 'cancelled' && $isOwner) {
        $stmt = $db->prepare("SELECT user_id FROM user_roles WHERE salon_id = ? AND role = 'owner'");
        $stmt->execute([$booking['salon_id']]);
        $owner = $stmt->fetch();

        if ($owner) {
            $stmt = $db->prepare("
    INSERT INTO notifications (id, user_id, salon_id, title, message, type, link)
    VALUES (?, ?, ?, ?, ?, 'booking', ?)
    ");
            $stmt->execute([
                Auth::generateUuid(),
                $owner['user_id'],
                $booking['salon_id'],
                'Appointment Cancelled',
                "A booking has been cancelled by the customer.",
                '/dashboard/appointments'
            ]);
        }
    }

    $stmt = $db->prepare("SELECT * FROM bookings WHERE id = ?");
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch();

    sendResponse(['booking' => $booking]);
}

// GET /api/bookings/:id/review - Get review for booking
if ($method === 'GET' && count($uriParts) === 3 && $uriParts[2] === 'review') {
    $bookingId = $uriParts[1];
    $stmt = $db->prepare("SELECT * FROM booking_reviews WHERE booking_id = ?");
    $stmt->execute([$bookingId]);
    $review = $stmt->fetch();
    sendResponse(['review' => $review]);
}

// POST /api/bookings/:id/review - Submit review
if ($method === 'POST' && count($uriParts) === 3 && $uriParts[2] === 'review') {
    try {
        $userData = Auth::getUserFromToken();
        if (!$userData)
            sendResponse(['error' => 'Unauthorized - No valid session found.'], 401);

        $bookingId = $uriParts[1];
        $data = getRequestBody();

        if (!$data)
            sendResponse(['error' => 'Empty or invalid request payload.'], 400);

        $rating = intval($data['rating'] ?? 5);
        $comment = $data['comment'] ?? '';

        // 1. Verify booking existence and ownership
        $stmt = $db->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ?");
        $stmt->execute([$bookingId, $userData['user_id']]);
        $booking = $stmt->fetch();

        if (!$booking)
            sendResponse(['error' => 'Booking not found or you do not have permission to review it.'], 404);
        if ($booking['status'] !== 'completed')
            sendResponse(['error' => 'You can only leave feedback for completed sessions.'], 400);

        // 3. Check for existing review
        $stmt = $db->prepare("SELECT id FROM booking_reviews WHERE booking_id = ?");
        $stmt->execute([$bookingId]);
        if ($stmt->fetch()) {
            sendResponse(['error' => 'Feedback has already been submitted for this appointment.'], 409);
        }

        // 4. Insert Review
        $reviewId = Auth::generateUuid();
        $stmt = $db->prepare("INSERT INTO booking_reviews (id, booking_id, user_id, salon_id, rating, comment) VALUES (?, ?,
    ?, ?, ?, ?)");
        $success = $stmt->execute([$reviewId, $bookingId, $userData['user_id'], $booking['salon_id'], $rating, $comment]);

        if (!$success) {
            $err = $stmt->errorInfo();
            throw new Exception("Database insertion failed: " . ($err[2] ?? 'Unknown registry error'));
        }

        sendResponse(['success' => true, 'message' => 'Thank you! Your feedback has been published.']);

    } catch (Exception $e) {
        sendResponse(['error' => 'Transmission Error: ' . $e->getMessage()], 500);
    }
}

// PUT /api/bookings/:id/review - Update review
if ($method === 'PUT' && count($uriParts) === 3 && $uriParts[2] === 'review') {
    try {
        $userData = Auth::getUserFromToken();
        if (!$userData)
            sendResponse(['error' => 'Unauthorized - No valid session found.'], 401);

        $bookingId = $uriParts[1];
        $data = getRequestBody();

        if (!$data)
            sendResponse(['error' => 'Empty or invalid request payload.'], 400);

        $rating = intval($data['rating'] ?? 5);
        $comment = $data['comment'] ?? '';

        // 1. Verify booking ownership
        $stmt = $db->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ?");
        $stmt->execute([$bookingId, $userData['user_id']]);
        $booking = $stmt->fetch();

        if (!$booking)
            sendResponse(['error' => 'Booking not found or you do not have permission to review it.'], 404);

        // 2. Check for existing review
        $stmt = $db->prepare("SELECT id FROM booking_reviews WHERE booking_id = ?");
        $stmt->execute([$bookingId]);
        $existingReview = $stmt->fetch();

        if (!$existingReview) {
            sendResponse(['error' => 'Review not found to update.'], 404);
        }

        // 3. Update Review
        $stmt = $db->prepare("UPDATE booking_reviews SET rating = ?, comment = ? WHERE booking_id = ?");
        $success = $stmt->execute([$rating, $comment, $bookingId]);

        if (!$success) {
            throw new Exception("Database update failed");
        }

        sendResponse(['success' => true, 'message' => 'Review updated successfully.']);

    } catch (Exception $e) {
        sendResponse(['error' => 'Transmission Error: ' . $e->getMessage()], 500);
    }
}

sendResponse(['error' => 'Booking route not found'], 404);