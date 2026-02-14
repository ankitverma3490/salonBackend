<?php
// Knowledge Base routes (Skin Care Tips / FAQs)

// GET /api/knowledge-base?salon_id=xxx&service_id=yyy - Get all tips/FAQs for a salon
if ($method === 'GET' && empty($uriParts[1])) {
    $salonId = $_GET['salon_id'] ?? null;
    $category = $_GET['category'] ?? null;
    $serviceId = $_GET['service_id'] ?? null;

    if (!$salonId) {
        sendResponse(['error' => 'salon_id is required'], 400);
    }

    $query = "SELECT * FROM salon_knowledge_base WHERE salon_id = ?";
    $params = [$salonId];

    if ($category) {
        $query .= " AND category = ?";
        $params[] = $category;
    }

    if ($serviceId) {
        // If service_id is provided, show general items (NULL) OR service-specific items
        $query .= " AND (service_id IS NULL OR service_id = ?)";
        $params[] = $serviceId;
    }

    $query .= " ORDER BY created_at DESC";

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $items = $stmt->fetchAll();

    sendResponse(['items' => $items]);
}

// POST /api/knowledge-base - Create new entry
if ($method === 'POST' && empty($uriParts[1])) {
    $data = getRequestBody();
    $userData = protectRoute(['owner', 'manager'], 'manage_salon', $data['salon_id'] ?? null);

    if (empty($data['salon_id']) || empty($data['title']) || empty($data['content'])) {
        sendResponse(['error' => 'Missing required fields: salon_id, title, content'], 400);
    }

    $id = Auth::generateUuid();
    $stmt = $db->prepare("
        INSERT INTO salon_knowledge_base (id, salon_id, service_id, category, title, content, is_active)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $id,
        $data['salon_id'],
        !empty($data['service_id']) ? $data['service_id'] : null,
        $data['category'] ?? 'Skin Care',
        $data['title'],
        $data['content'],
        isset($data['is_active']) ? (int) $data['is_active'] : 1
    ]);

    $stmt = $db->prepare("SELECT * FROM salon_knowledge_base WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();

    sendResponse(['item' => $item], 201);
}

// PUT /api/knowledge-base/:id - Update entry
if ($method === 'PUT' && !empty($uriParts[1])) {
    $id = $uriParts[1];

    // Check ownership
    $stmt = $db->prepare("SELECT salon_id FROM salon_knowledge_base WHERE id = ?");
    $stmt->execute([$id]);
    $existing = $stmt->fetch();

    if (!$existing) {
        sendResponse(['error' => 'Entry not found'], 404);
    }

    $userData = protectRoute(['owner', 'manager'], 'manage_salon', $existing['salon_id']);
    $data = getRequestBody();

    $fields = [];
    $params = [];
    $allowedFields = ['category', 'title', 'content', 'is_active', 'service_id'];

    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $fields[] = "$field = ?";
            $params[] = $data[$field];
        }
    }

    if (empty($fields)) {
        sendResponse(['error' => 'No fields to update'], 400);
    }

    $params[] = $id;
    $sql = "UPDATE salon_knowledge_base SET " . implode(', ', $fields) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    $stmt = $db->prepare("SELECT * FROM salon_knowledge_base WHERE id = ?");
    $stmt->execute([$id]);
    $updated = $stmt->fetch();

    sendResponse(['item' => $updated]);
}

// DELETE /api/knowledge-base/:id - Delete entry
if ($method === 'DELETE' && !empty($uriParts[1])) {
    $id = $uriParts[1];

    // Check ownership
    $stmt = $db->prepare("SELECT salon_id FROM salon_knowledge_base WHERE id = ?");
    $stmt->execute([$id]);
    $existing = $stmt->fetch();

    if (!$existing) {
        sendResponse(['error' => 'Entry not found'], 404);
    }

    protectRoute(['owner', 'manager'], 'manage_salon', $existing['salon_id']);

    $stmt = $db->prepare("DELETE FROM salon_knowledge_base WHERE id = ?");
    $stmt->execute([$id]);

    sendResponse(['message' => 'Entry deleted successfully']);
}
