<?php
// Platform Products management (Super Admin only for mutation, Public for viewing)

$isMutation = in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH']);

// Only protect mutation routes for admin
if ($isMutation) {
    $userData = protectRoute(['admin']);
}

// GET /api/platform_products - List products
if ($method === 'GET' && count($uriParts) === 1) {
    $audience = $_GET['audience'] ?? null;
    $category = $_GET['category'] ?? null;

    $query = "SELECT * FROM platform_products WHERE is_active = 1";
    $params = [];

    if ($audience) {
        $query .= " AND (target_audience = ? OR target_audience = 'both')";
        $params[] = $audience;
    }

    if ($category) {
        $query .= " AND category = ?";
        $params[] = $category;
    }

    $query .= " ORDER BY created_at DESC";

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    sendResponse($stmt->fetchAll());
}

// GET /api/platform_products/:id - Single product
if ($method === 'GET' && count($uriParts) === 2) {
    $id = $uriParts[1];
    $stmt = $db->prepare("SELECT * FROM platform_products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if (!$product)
        sendResponse(['error' => 'Product not found'], 404);
    sendResponse($product);
}

// POST /api/platform_products - Create (Admin only)
if ($method === 'POST' && count($uriParts) === 1) {
    $data = getRequestBody();

    if (!isset($data['name']) || !isset($data['price'])) {
        sendResponse(['error' => 'Missing required fields'], 400);
    }

    $id = Auth::generateUuid();
    $stmt = $db->prepare("
        INSERT INTO platform_products (id, name, description, price, discount, stock_quantity, image_url, image_public_id, image_url_2, image_2_public_id, image_url_3, image_3_public_id, image_url_4, image_4_public_id, category, brand, target_audience, features)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $id,
        $data['name'],
        $data['description'] ?? null,
        $data['price'],
        $data['discount'] ?? 0,
        $data['stock_quantity'] ?? 0,
        $data['image_url'] ?? null,
        $data['image_public_id'] ?? null,
        $data['image_url_2'] ?? null,
        $data['image_2_public_id'] ?? null,
        $data['image_url_3'] ?? null,
        $data['image_3_public_id'] ?? null,
        $data['image_url_4'] ?? null,
        $data['image_4_public_id'] ?? null,
        $data['category'] ?? 'General',
        $data['brand'] ?? null,
        $data['target_audience'] ?? 'both',
        $data['features'] ?? null
    ]);

    sendResponse(['message' => 'Product created', 'id' => $id]);
}

// PUT /api/platform_products/:id - Update (Admin only)
if ($method === 'PUT' && count($uriParts) === 2) {
    $id = $uriParts[1];
    $data = getRequestBody();

    $stmt = $db->prepare("
        UPDATE platform_products SET
            name = COALESCE(?, name),
            description = COALESCE(?, description),
            features = COALESCE(?, features),
            price = COALESCE(?, price),
            discount = COALESCE(?, discount),
            stock_quantity = COALESCE(?, stock_quantity),
            image_url = COALESCE(?, image_url),
            image_public_id = COALESCE(?, image_public_id),
            image_url_2 = COALESCE(?, image_url_2),
            image_2_public_id = COALESCE(?, image_2_public_id),
            image_url_3 = COALESCE(?, image_url_3),
            image_3_public_id = COALESCE(?, image_3_public_id),
            image_url_4 = COALESCE(?, image_url_4),
            image_4_public_id = COALESCE(?, image_4_public_id),
            category = COALESCE(?, category),
            brand = COALESCE(?, brand),
            target_audience = COALESCE(?, target_audience),
            is_active = COALESCE(?, is_active)
        WHERE id = ?
    ");

    $stmt->execute([
        $data['name'] ?? null,
        $data['description'] ?? null,
        $data['features'] ?? null,
        $data['price'] ?? null,
        $data['discount'] ?? null,
        $data['stock_quantity'] ?? null,
        $data['image_url'] ?? null,
        $data['image_public_id'] ?? null,
        $data['image_url_2'] ?? null,
        $data['image_2_public_id'] ?? null,
        $data['image_url_3'] ?? null,
        $data['image_3_public_id'] ?? null,
        $data['image_url_4'] ?? null,
        $data['image_4_public_id'] ?? null,
        $data['category'] ?? null,
        $data['brand'] ?? null,
        $data['target_audience'] ?? null,
        $data['is_active'] ?? null,
        $id
    ]);

    sendResponse(['message' => 'Product updated']);
}

// DELETE /api/platform_products/:id - Delete (Admin only)
if ($method === 'DELETE' && count($uriParts) === 2) {
    $id = $uriParts[1];
    $stmt = $db->prepare("DELETE FROM platform_products WHERE id = ?");
    $stmt->execute([$id]);
    sendResponse(['message' => 'Product deleted']);
}

sendResponse(['error' => 'Product route not found'], 404);
