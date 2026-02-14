<?php
/**
 * PHP Built-in Server Router Script
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 🚀 GLOBAL CORS HEADERS (Must be at the very top)
$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: $origin");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin, Cache-Control, Pragma');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

// Adjust root based on where router.php is located
$projectRoot = dirname(__DIR__);
$file = $projectRoot . $uri;

error_log("[Router Debug] URI: $uri, File: $file");

// If the request is for a physical file or directory, serve it as is
if ($uri !== '/' && (file_exists($file) || is_dir($file))) {
    return false;
}

// Otherwise, route everything to the API index.php if it starts with /api or /backend/api
if (strpos($uri, '/api') === 0 || strpos($uri, '/backend/api') === 0) {
    // If it's the short form /api/..., we need to make sure index.php knows how to handle it
    $_SERVER['SCRIPT_NAME'] = '/backend/api/index.php';
    require_once __DIR__ . '/api/index.php';
    return;
}

// Fallback for other routes (if any)
return false;
