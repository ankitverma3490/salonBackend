<?php
// Debug script to verify token and admin status
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Auth.php';

$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? 'NOT FOUND';

$userData = Auth::getUserFromToken();
$db = Database::getInstance()->getConnection();

$adminStatus = 'N/A';
if ($userData) {
    $stmt = $db->prepare("SELECT id FROM platform_admins WHERE user_id = ? AND is_active = 1");
    $stmt->execute([$userData['user_id']]);
    $adminStatus = $stmt->fetch() ? 'YES' : 'NO';
}

echo json_encode([
    'headers' => $headers,
    'auth_header_extracted' => $authHeader,
    'user_data_from_token' => $userData,
    'is_admin_in_db' => $adminStatus,
    'server_vars' => [
        'HTTP_AUTHORIZATION' => $_SERVER['HTTP_AUTHORIZATION'] ?? 'null',
        'REDIRECT_HTTP_AUTHORIZATION' => $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? 'null',
    ]
]);
?>