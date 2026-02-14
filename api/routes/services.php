<?php
// Service routes

// GET /api/services?salon_id=xxx - Get services by salon
if ($method === 'GET' && count($uriParts) === 1) {
    $salonId = $_GET['salon_id'] ?? null;
    $includeInactive = isset($_GET['include_inactive']) && $_GET['include_inactive'] == '1';

    if ($salonId) {
        $query = "SELECT * FROM services WHERE salon_id = ?";
        if (!$includeInactive) {
            $query .= " AND is_active = 1";
        }
        $query .= " ORDER BY category, name";

        $stmt = $db->prepare($query);
        $stmt->execute([$salonId]);
        $services = $stmt->fetchAll();
    }
    else {
        // Global fetch - for "All Services" page if no salon specified
        // Only show active services from active salons
        // We use RAND() to show 'mixed' services across different categories and salons
        $stmt = $db->prepare("
            SELECT s.*, 
                   sln.name as salon_name, 
                   sln.city as salon_city, 
                   sln.logo_url as salon_logo_url, 
                   sln.cover_image_url as salon_cover_url, 
                   p.full_name as owner_name,
                   COALESCE(AVG(r.rating), 0) as rating,
                   COUNT(r.id) as review_count
            FROM services s
            JOIN salons sln ON s.salon_id = sln.id
            LEFT JOIN user_roles ur ON sln.id = ur.salon_id AND ur.role = 'owner'
            LEFT JOIN profiles p ON ur.user_id = p.user_id
            LEFT JOIN bookings b ON s.id = b.service_id
            LEFT JOIN booking_reviews r ON b.id = r.booking_id
            WHERE s.is_active = 1 AND sln.is_active = 1
            GROUP BY s.id, sln.name, sln.city, sln.logo_url, sln.cover_image_url, p.full_name
            ORDER BY RAND()
        ");
        $stmt->execute();
        $services = $stmt->fetchAll();
    }

    sendResponse(['services' => $services]);
}

// GET /api/services/categories - Get unique service categories
if ($method === 'GET' && count($uriParts) === 2 && $uriParts[1] === 'categories') {
    try {
        $stmt = $db->prepare("
            SELECT category as title, MIN(image_url) as image, COUNT(*) as service_count
            FROM services 
            WHERE is_active = 1 AND category IS NOT NULL AND category != ''
            GROUP BY category
            ORDER BY service_count DESC
        ");
        $stmt->execute();
        $categories = $stmt->fetchAll();

        $formatted = array_map(function ($cat) {
            return [
            'id' => strtolower(str_replace(' ', '-', $cat['title'])),
            'title' => $cat['title'],
            'image' => $cat['image'] ?: 'https://images.unsplash.com/photo-1560750588-73207b1ef5b8?w=800&auto=format&fit=crop',
            'link' => "/salons?category=" . urlencode($cat['title']),
            'isComingSoon' => false,
            'isGrayscale' => false
            ];
        }, $categories);

        sendResponse(['categories' => $formatted]);
    }
    catch (PDOException $e) {
        sendResponse(['error' => 'Query failed: ' . $e->getMessage()], 500);
    }
}

// GET /api/services/:id - Get service by ID
if ($method === 'GET' && count($uriParts) === 2) {
    $serviceId = $uriParts[1];
    $stmt = $db->prepare("
        SELECT s.*, 
               sln.name as salon_name, 
               sln.address as salon_address, 
               sln.city as salon_city, 
               sln.state as salon_state,
               sln.pincode as salon_pincode,
               sln.phone as salon_phone,
               sln.email as salon_email,
               sln.logo_url as salon_logo_url,
               sln.cover_image_url as salon_cover_url,
               COALESCE(AVG(r.rating), 0) as rating,
               COUNT(r.id) as review_count
        FROM services s
        JOIN salons sln ON s.salon_id = sln.id
        LEFT JOIN bookings b ON s.id = b.service_id
        LEFT JOIN booking_reviews r ON b.id = r.booking_id
        WHERE s.id = ?
        GROUP BY s.id, sln.name, sln.address, sln.city, sln.state, sln.pincode, sln.phone, sln.email, sln.logo_url, sln.cover_image_url
    ");
    $stmt->execute([$serviceId]);
    $service = $stmt->fetch();

    if (!$service) {
        sendResponse(['error' => 'Service not found'], 404);
    }

    sendResponse(['service' => $service]);
}

// POST /api/services - Create new service
if ($method === 'POST' && count($uriParts) === 1) {
    $data = getRequestBody();
    $salonId = $data['salon_id'];

    $userData = protectRoute(['owner', 'manager'], 'manage_services', $salonId);

    if (!$membershipService->canAddService($salonId)) {
        sendResponse(['error' => 'Plan limit reached. Please upgrade your subscription.'], 403);
    }

    $serviceId = Auth::generateUuid();
    $stmt = $db->prepare("
        INSERT INTO services (id, salon_id, name, description, price, duration_minutes, category, image_url, image_public_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $serviceId,
        $salonId,
        $data['name'],
        $data['description'] ?? null,
        $data['price'],
        $data['duration_minutes'],
        $data['category'] ?? null,
        $data['image_url'] ?? null,
        $data['image_public_id'] ?? null
    ]);

    $stmt = $db->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$serviceId]);
    $service = $stmt->fetch();

    // Notify subscribers
    $newsletterService->notifySubscribers('service', $data['name'], "Price: RM " . $data['price']);

    sendResponse(['service' => $service], 201);
}

// PUT /api/services/:id - Update service
if ($method === 'PUT' && count($uriParts) === 2) {
    $serviceId = $uriParts[1];
    $data = getRequestBody();

    // Get service and check permission
    $stmt = $db->prepare("SELECT salon_id FROM services WHERE id = ?");
    $stmt->execute([$serviceId]);
    $service = $stmt->fetch();

    if (!$service) {
        sendResponse(['error' => 'Service not found'], 404);
    }

    $userData = protectRoute(['owner', 'manager'], 'manage_services', $service['salon_id']);

    $stmt = $db->prepare("
        UPDATE services SET
            name = ?, description = ?, price = ?, duration_minutes = ?, category = ?, image_url = ?, image_public_id = ?, is_active = ?
        WHERE id = ?
    ");
    $stmt->execute([
        $data['name'],
        $data['description'] ?? null,
        $data['price'],
        $data['duration_minutes'],
        $data['category'] ?? null,
        $data['image_url'] ?? null,
        $data['image_public_id'] ?? null,
        $data['is_active'] ?? 1,
        $serviceId
    ]);

    $stmt = $db->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$serviceId]);
    $service = $stmt->fetch();

    sendResponse(['service' => $service]);
}

// DELETE /api/services/:id - Delete service
if ($method === 'DELETE' && count($uriParts) === 2) {
    $serviceId = $uriParts[1];

    // Get service and check permission
    $stmt = $db->prepare("SELECT salon_id, image_public_id FROM services WHERE id = ?");
    $stmt->execute([$serviceId]);
    $service = $stmt->fetch();

    if (!$service) {
        sendResponse(['error' => 'Service not found'], 404);
    }

    $userData = protectRoute(['owner', 'manager'], 'manage_services', $service['salon_id']);

    // Delete from Cloudinary if exists
    if (!empty($service['image_public_id'])) {
        global $cloudinaryService;
        $cloudinaryService->deleteFile($service['image_public_id']);
    }

    $stmt = $db->prepare("DELETE FROM services WHERE id = ?");
    $stmt->execute([$serviceId]);

    sendResponse(['message' => 'Service deleted successfully']);
}

sendResponse(['error' => 'Service route not found'], 404);
