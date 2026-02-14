<?php
// Test authentication endpoints
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Testing Auth Endpoints ===\n\n";

// Test signup
echo "1. Testing SIGNUP endpoint...\n";
$signupData = [
    'email' => 'testuser_' . time() . '@test.com',
    'password' => 'Test123!',
    'full_name' => 'Test User',
    'user_type' => 'customer'
];

$ch = curl_init('http://localhost/backend/api/auth/signup');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($signupData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n\n";

// Test login with existing user
echo "2. Testing LOGIN endpoint...\n";
$loginData = [
    'email' => 'aman@gmail.com',
    'password' => 'aman'
];

$ch = curl_init('http://localhost/backend/api/auth/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n\n";

// Parse and test token
$data = json_decode($response, true);
if (isset($data['data']['token'])) {
    $token = $data['data']['token'];
    echo "3. Testing /auth/me with token...\n";

    $ch = curl_init('http://localhost/backend/api/auth/me');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "HTTP Code: $httpCode\n";
    echo "Response: $response\n";
}
