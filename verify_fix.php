<?php
// Mock request environment for admin stats
$_SERVER['REQUEST_METHOD'] = 'GET';
$uri = '/api/admin/stats';
$method = 'GET';
$uriParts = ['admin', 'stats'];

require_once __DIR__ . '/api/routes/admin.php';

// If this runs without error, it means the SQL is fixed.
// The route calls sendResponse() which exits, so we wrap it if needed.
function protectRoute($roles)
{
    return ['role' => 'admin'];
}
function sendResponse($data)
{
    echo "SUCCESS: " . json_encode($data, JSON_PRETTY_PRINT);
    exit;
}
