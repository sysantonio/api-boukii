<?php

// Debug middleware execution and context
require_once 'vendor/autoload.php';

echo "🔍 Debugging Middleware and Context\n";
echo "===================================\n\n";

// Step 1: Login to get token
echo "1. Getting authentication token...\n";

$loginUrl = 'http://api.boukii.test/api/v5/auth/check-user';
$loginData = [
    'email' => 'admin@escuela-test-v5.com',
    'password' => 'password123'
];

$loginResponse = makeHttpRequest('POST', $loginUrl, $loginData);

if (!$loginResponse['success']) {
    echo "❌ Login failed: " . $loginResponse['message'] . "\n";
    exit(1);
}

$token = $loginResponse['data']['access_token'] ?? null;
if (!$token) {
    echo "❌ No token received\n";
    exit(1);
}

echo "✅ Token received: " . substr($token, 0, 20) . "...\n\n";

// Step 2: Test just getting seasons (should work with middleware)
echo "2. Testing GET /api/v5/seasons (should work)...\n";

$getSeasonsResponse = makeHttpRequest('GET', 'http://api.boukii.test/api/v5/seasons', null, $token);
echo "GET seasons response:\n";
print_r($getSeasonsResponse);
echo "\n";

// Step 3: Test minimal season creation with debug headers
echo "3. Testing POST /api/v5/seasons with minimal data...\n";

$minimalData = [
    'name' => 'Debug Test Season ' . date('H:i:s'),
    'start_date' => date('Y-m-d', strtotime('+1 day')),
    'end_date' => date('Y-m-d', strtotime('+30 days'))
];

echo "Request data:\n";
print_r($minimalData);
echo "\n";

$createResponse = makeHttpRequestWithDebug('POST', 'http://api.boukii.test/api/v5/seasons', $minimalData, $token);
echo "Create season response:\n";
print_r($createResponse);

function makeHttpRequest($method, $url, $data = null, $token = null) {
    $curl = curl_init();
    
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json'
    ];
    
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    
    if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    $decoded = json_decode($response, true);
    return $decoded ?: ['error' => 'Invalid JSON', 'raw' => $response, 'http_code' => $httpCode];
}

function makeHttpRequestWithDebug($method, $url, $data = null, $token = null) {
    $curl = curl_init();
    
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json'
    ];
    
    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HEADER => true,
        CURLOPT_VERBOSE => true
    ]);
    
    if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    curl_close($curl);
    
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    
    echo "HTTP Code: $httpCode\n";
    echo "Response Headers:\n$headers\n";
    echo "Response Body:\n$body\n\n";
    
    $decoded = json_decode($body, true);
    return $decoded ?: ['error' => 'Invalid JSON', 'raw' => $body, 'http_code' => $httpCode];
}