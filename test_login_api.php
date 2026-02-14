<?php
/**
 * Test Login API Endpoint
 */

$url = 'http://localhost:8000/backend/api/auth/login';

echo "Testing Login API...\n\n";

// Test with superadmin credentials
$credentials = [
    'email' => 'superadmin@salon.com',
    'password' => 'admin123'
];

echo "Attempting login with:\n";
echo "  Email: {$credentials['email']}\n";
echo "  Password: {$credentials['password']}\n\n";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($credentials));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Origin: http://localhost:5174'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "=== Response ===\n";
echo "HTTP Status: {$httpCode}\n\n";

if ($error) {
    echo "Error: {$error}\n";
}
else {
    $data = json_decode($response, true);
    echo json_encode($data, JSON_PRETTY_PRINT) . "\n\n";

    if ($httpCode === 200 && isset($data['data']['token'])) {
        echo "✅ LOGIN SUCCESSFUL!\n";
        echo "Token: " . substr($data['data']['token'], 0, 50) . "...\n";
        echo "User: {$data['data']['user']['email']}\n";
        echo "Role: {$data['data']['user']['role']}\n";
    }
    else {
        echo "❌ LOGIN FAILED\n";
        if (isset($data['error'])) {
            echo "Error: {$data['error']}\n";
        }
    }
}
