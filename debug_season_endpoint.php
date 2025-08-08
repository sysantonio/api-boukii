<?php

// Debug script to test the season endpoint step by step
require_once 'vendor/autoload.php';

echo "🔍 Debugging Season Creation Endpoint\n";
echo "====================================\n\n";

// Step 1: Test login and token creation
echo "1. Testing login and token...\n";

$loginUrl = 'http://api.boukii.test/api/v5/auth/check-user';
$loginData = [
    'email' => 'admin@escuela-test-v5.com',
    'password' => 'password123'
];

$loginResponse = makeHttpRequest('POST', $loginUrl, $loginData);
echo "Login response:\n";
print_r($loginResponse);

if (!$loginResponse['success']) {
    echo "❌ Login failed, stopping.\n";
    exit(1);
}

$token = $loginResponse['data']['access_token'] ?? null;
if (!$token) {
    echo "❌ No token received, stopping.\n";
    exit(1);
}

echo "✅ Token received: " . substr($token, 0, 20) . "...\n\n";

// Step 2: Test minimal season creation
echo "2. Testing minimal season creation...\n";

$seasonUrl = 'http://api.boukii.test/api/v5/seasons';
$minimalSeasonData = [
    'name' => 'Test Season ' . date('H:i:s'),
    'start_date' => date('Y-m-d', strtotime('+1 day')),
    'end_date' => date('Y-m-d', strtotime('+30 days'))
];

echo "Sending minimal data:\n";
print_r($minimalSeasonData);
echo "\n";

$seasonResponse = makeHttpRequest('POST', $seasonUrl, $minimalSeasonData, $token);
echo "Season creation response:\n";
print_r($seasonResponse);

if ($seasonResponse['success']) {
    echo "✅ Season created successfully!\n";
    echo "Season ID: " . $seasonResponse['data']['id'] . "\n";
    echo "Season Name: " . $seasonResponse['data']['name'] . "\n";
} else {
    echo "❌ Season creation failed.\n";
    if (isset($seasonResponse['errors'])) {
        echo "Validation errors:\n";
        foreach ($seasonResponse['errors'] as $field => $messages) {
            echo "  - $field: " . implode(', ', $messages) . "\n";
        }
    }
}

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
        CURLOPT_VERBOSE => true
    ]);
    
    if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);
    curl_close($curl);
    
    echo "HTTP Code: $httpCode\n";
    if ($error) {
        echo "cURL Error: $error\n";
    }
    
    $decoded = json_decode($response, true);
    if (!$decoded) {
        echo "Raw response: $response\n";
        return [
            'success' => false,
            'message' => 'Invalid JSON response',
            'raw_response' => $response
        ];
    }
    
    return $decoded;
}