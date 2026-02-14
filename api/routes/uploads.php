<?php
/**
 * 🚀 FILE UPLOAD ROUTER
 */

// POST /api/uploads - Upload a file
if ($method === 'POST') {
    $userData = Auth::getUserFromToken();
    if (!$userData) {
        sendResponse(['error' => 'Unauthorized'], 401);
    }

    if (!isset($_FILES['file'])) {
        sendResponse(['error' => 'No file uploaded'], 400);
    }

    $file = $_FILES['file'];
    $allowedTypes = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
        'image/avif',
        'application/pdf'
    ];

    if (!in_array($file['type'], $allowedTypes)) {
        sendResponse(['error' => 'Invalid file type. Only JPEG, PNG, WEBP, GIF, AVIF and PDF are allowed.'], 400);
    }

    // Limit size to 10MB for Cloudinary
    if ($file['size'] > 10 * 1024 * 1024) {
        sendResponse(['error' => 'File too large. Maximum size is 10MB.'], 400);
    }

    global $cloudinaryService;

    // Determine resource type
    $resourceType = strpos($file['type'], 'pdf') !== false ? 'raw' : 'image';

    $result = $cloudinaryService->uploadFile($file['tmp_name'], [
        'folder' => 'salon_uploads',
        'resource_type' => 'auto' // Let Cloudinary decide
    ]);

    if (isset($result['success']) && $result['success']) {
        sendResponse([
            'message' => 'File uploaded successfully to Cloudinary',
            'url' => $result['url'],
            'secure_url' => $result['url'],
            'public_id' => $result['public_id'],
            'resource_type' => $result['resource_type'],
            'fileName' => basename($result['url'])
        ], 201);
    } else {
        error_log("Cloudinary Upload Error: " . ($result['error'] ?? 'Unknown error'));
        sendResponse(['error' => 'Failed to upload file to Cloudinary: ' . ($result['error'] ?? 'Unknown error')], 500);
    }
}

sendResponse(['error' => 'Upload route only supports POST'], 405);
