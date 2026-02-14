<?php
// Search routes

// GET /api/search?q=xxx - Global search
if ($method === 'GET' && count($uriParts) === 1) {
    $q = $_GET['q'] ?? '';

    if (empty($q) || strlen($q) < 2) {
        sendResponse([
            'suggestions' => [],
            'products' => [],
            'salons' => [],
            'services' => []
        ]);
    }

    $searchTerm = "%$q%";
    $results = [
        'suggestions' => [],
        'products' => [],
        'salons' => [],
        'services' => []
    ];

    try {
        // 1. Search Salons
        $stmt = $db->prepare("
            SELECT s.id, s.name, s.logo_url, s.city,
                   COALESCE(AVG(r.rating), 0) as rating,
                   COUNT(r.id) as review_count
            FROM salons s
            LEFT JOIN booking_reviews r ON s.id = r.salon_id
            WHERE (s.name LIKE ? OR s.description LIKE ?) AND s.is_active = 1 
            GROUP BY s.id, s.name, s.logo_url, s.city
            LIMIT 5
        ");
        $stmt->execute([$searchTerm, $searchTerm]);
        $results['salons'] = $stmt->fetchAll();

        // 2. Search Services
        $stmt = $db->prepare("
            SELECT s.id, s.name, s.price, s.image_url, sln.name as salon_name,
                   COALESCE(AVG(r.rating), 0) as rating,
                   COUNT(r.id) as review_count
            FROM services s 
            JOIN salons sln ON s.salon_id = sln.id 
            LEFT JOIN bookings b ON s.id = b.service_id
            LEFT JOIN booking_reviews r ON b.id = r.booking_id
            WHERE (s.name LIKE ? OR s.description LIKE ?) AND s.is_active = 1 AND sln.is_active = 1 
            GROUP BY s.id, s.name, s.price, s.image_url, sln.name
            LIMIT 10
        ");
        $stmt->execute([$searchTerm, $searchTerm]);
        $results['services'] = $stmt->fetchAll();

        // 3. Search Platform Products
        $stmt = $db->prepare("SELECT id, name, price, image_url 
                             FROM platform_products 
                             WHERE (name LIKE ? OR description LIKE ?) AND is_active = 1 
                             LIMIT 10");
        $stmt->execute([$searchTerm, $searchTerm]);
        $results['products'] = $stmt->fetchAll();

        // 4. Generate Suggestions (Unique names that match)
        $suggestions = [];

        // From Salons
        foreach ($results['salons'] as $s)
            $suggestions[] = $s['name'];
        // From Services
        foreach ($results['services'] as $s)
            $suggestions[] = $s['name'];
        // From Products
        foreach ($results['products'] as $p)
            $suggestions[] = $p['name'];

        $results['suggestions'] = array_values(array_unique(array_slice($suggestions, 0, 8)));

        sendResponse($results);
    }
    catch (PDOException $e) {
        sendResponse(['error' => 'Search failed: ' . $e->getMessage()], 500);
    }
}
