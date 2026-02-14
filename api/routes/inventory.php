<?php
// Salon Inventory management (Salon Owners and Staff)

// Use consistent mapped roles: 'owner' instead of 'salon_owner'
$userData = protectRoute(['owner', 'staff', 'manager', 'super_admin']);
// Multi-tenant Security: Ensure user has access to this SPECIFIC salon
$data = getRequestBody();
$salonId = $_GET['salon_id'] ?? $data['salon_id'] ?? null;

if (!$salonId) {
    sendResponse(['error' => 'Salon ID is required'], 400);
}

// Multi-tenant Security: Ensure user has access to this SPECIFIC salon
// This prevents Salon Owner A from managing Salon Owner B's products
if ($userData['role'] !== 'super_admin' && $userData['role'] !== 'admin') {
    $stmt = $db->prepare("SELECT role FROM user_roles WHERE user_id = ? AND salon_id = ?");
    $stmt->execute([$userData['user_id'], $salonId]);
    $role = $stmt->fetch();
    if (!$role) {
        sendResponse(['error' => 'Unauthorized: You do not have permission to manage this salon\'s inventory.'], 403);
    }
}

// GET /api/inventory - List products (isolated by salon_id)
if ($method === 'GET' && count($uriParts) === 1) {
    if (isset($_GET['suppliers_only'])) {
        $stmt = $db->prepare("SELECT * FROM salon_suppliers WHERE salon_id = ? ORDER BY name ASC");
        $stmt->execute([$salonId]);
        sendResponse($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    $query = "SELECT * FROM salon_inventory WHERE salon_id = ?";
    $params = [$salonId];

    $category = $_GET['category'] ?? null;
    if ($category) {
        $query .= " AND category = ?";
        $params[] = $category;
    }

    $query .= " ORDER BY name ASC";

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    sendResponse($stmt->fetchAll(PDO::FETCH_ASSOC));
}

// GET /api/inventory/:id - Single product
if ($method === 'GET' && count($uriParts) === 2) {
    $id = $uriParts[1];
    $stmt = $db->prepare("SELECT * FROM salon_inventory WHERE id = ? AND salon_id = ?");
    $stmt->execute([$id, $salonId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product)
        sendResponse(['error' => 'Product not found'], 404);
    sendResponse($product);
}

// POST /api/inventory - Create product
if ($method === 'POST' && count($uriParts) === 1) {

    if (!isset($data['name'])) {
        sendResponse(['error' => 'Product name is required'], 400);
    }

    if (isset($data['is_supplier']) && $data['is_supplier'] === true) {
        $id = Auth::generateUuid();
        $stmt = $db->prepare("
            INSERT INTO salon_suppliers (id, salon_id, name, contact_person, phone, email, address)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $id,
            $salonId,
            $data['name'],
            $data['contact_person'] ?? null,
            $data['phone'] ?? null,
            $data['email'] ?? null,
            $data['address'] ?? null
        ]);
        sendResponse(['message' => 'Supplier created', 'id' => $id]);
    }

    $id = Auth::generateUuid();
    $stmt = $db->prepare("
        INSERT INTO salon_inventory (id, salon_id, name, category, stock_quantity, min_stock_level, unit_price, supplier_name, last_restocked_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $id,
        $salonId,
        $data['name'],
        $data['category'] ?? 'General',
        $data['stock_quantity'] ?? 0,
        $data['min_stock_level'] ?? 5,
        $data['unit_price'] ?? 0.00,
        $data['supplier_name'] ?? null,
        null // last_restocked_at defaults to null on creation
    ]);

    sendResponse(['message' => 'Product created', 'id' => $id]);
}

// PUT /api/inventory/:id - Update product
if ($method === 'PUT' && count($uriParts) === 2) {
    $id = $uriParts[1];

    // Check if updating supplier
    if (isset($data['is_supplier']) && $data['is_supplier'] === true) {
        $stmt = $db->prepare("
            UPDATE salon_suppliers SET
                name = COALESCE(?, name),
                contact_person = COALESCE(?, contact_person),
                phone = COALESCE(?, phone),
                email = COALESCE(?, email),
                address = COALESCE(?, address)
            WHERE id = ? AND salon_id = ?
        ");
        $stmt->execute([
            $data['name'] ?? null,
            $data['contact_person'] ?? null,
            $data['phone'] ?? null,
            $data['email'] ?? null,
            $data['address'] ?? null,
            $id,
            $salonId
        ]);
        sendResponse(['message' => 'Supplier updated']);
    }

    $stmt = $db->prepare("
        UPDATE salon_inventory SET
            name = COALESCE(?, name),
            category = COALESCE(?, category),
            stock_quantity = COALESCE(?, stock_quantity),
            min_stock_level = COALESCE(?, min_stock_level),
            unit_price = COALESCE(?, unit_price),
            supplier_name = COALESCE(?, supplier_name),
            last_restocked_at = COALESCE(?, last_restocked_at)
        WHERE id = ? AND salon_id = ?
    ");

    $stmt->execute([
        $data['name'] ?? null,
        $data['category'] ?? null,
        $data['stock_quantity'] ?? null,
        $data['min_stock_level'] ?? null,
        $data['unit_price'] ?? null,
        $data['supplier_name'] ?? null,
        $data['last_restocked_at'] ?? null,
        $id,
        $salonId
    ]);

    sendResponse(['message' => 'Product updated']);
}

// DELETE /api/inventory/:id - Delete product
if ($method === 'DELETE' && count($uriParts) === 2) {
    $id = $uriParts[1];

    if (isset($_GET['is_supplier']) && $_GET['is_supplier'] == '1') {
        $stmt = $db->prepare("DELETE FROM salon_suppliers WHERE id = ? AND salon_id = ?");
        $stmt->execute([$id, $salonId]);
        sendResponse(['message' => 'Supplier deleted']);
    }

    $stmt = $db->prepare("DELETE FROM salon_inventory WHERE id = ? AND salon_id = ?");
    $stmt->execute([$id, $salonId]);
    sendResponse(['message' => 'Product deleted']);
}

sendResponse(['error' => 'Inventory route not found'], 404);
