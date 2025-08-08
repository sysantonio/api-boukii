<?php

// Quick manual test for season creation endpoint
// This should be run with: php test_season_creation.php

require_once 'vendor/autoload.php';

$apiBaseUrl = 'http://api.boukii.test/api/v5';

// Test data
$loginData = [
    'email' => 'admin@escuela-test-v5.com',
    'password' => 'password123'
];

$seasonData = [
    'name' => 'Test Season ' . date('Y-m-d H:i:s'),
    'description' => 'A test season created via API',
    'start_date' => date('Y-m-d', strtotime('+30 days')),
    'end_date' => date('Y-m-d', strtotime('+120 days')),
    'is_active' => false,
    'max_capacity' => 100
];

echo "🧪 Testing Season Creation API\n";
echo "================================\n\n";

// Step 1: Login to get token
echo "1. 🔐 Logging in...\n";
$loginResponse = makeRequest('POST', "$apiBaseUrl/auth/check-user", $loginData);

if (!$loginResponse['success']) {
    echo "❌ Login failed: " . $loginResponse['message'] . "\n";
    exit(1);
}

$token = $loginResponse['data']['access_token'] ?? null;
if (!$token) {
    echo "❌ No access token received\n";
    exit(1);
}

echo "✅ Login successful, got token: " . substr($token, 0, 20) . "...\n\n";

// Step 2: Create season
echo "2. 🌟 Creating season...\n";
echo "Season data:\n";
print_r($seasonData);
echo "\n";

$seasonResponse = makeRequest('POST', "$apiBaseUrl/seasons", $seasonData, $token);

if (!$seasonResponse['success']) {
    echo "❌ Season creation failed: " . $seasonResponse['message'] . "\n";
    if (isset($seasonResponse['errors'])) {
        echo "Validation errors:\n";
        print_r($seasonResponse['errors']);
    }
    exit(1);
}

echo "✅ Season created successfully!\n";
echo "Season data:\n";
print_r($seasonResponse['data']);

// Step 3: Verify season was created by fetching it
echo "\n3. 🔍 Verifying season was created...\n";
$seasonId = $seasonResponse['data']['id'];
$fetchResponse = makeRequest('GET', "$apiBaseUrl/seasons/$seasonId", null, $token);

if (!$fetchResponse['success']) {
    echo "❌ Failed to fetch created season: " . $fetchResponse['message'] . "\n";
    exit(1);
}

echo "✅ Season fetched successfully!\n";
echo "Fetched season name: " . $fetchResponse['data']['name'] . "\n";

echo "\n🎉 All tests passed! The Season API is working correctly.\n";
echo "✨ Key success: NO school_id was required in the request payload!\n";

function makeRequest($method, $url, $data = null, $token = null) {
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
    $error = curl_error($curl);
    curl_close($curl);
    
    if ($error) {
        return [
            'success' => false,
            'message' => 'cURL Error: ' . $error,
            'http_code' => $httpCode
        ];
    }
    
    $decoded = json_decode($response, true);
    if (!$decoded) {
        return [
            'success' => false,
            'message' => 'Invalid JSON response',
            'http_code' => $httpCode,
            'raw_response' => $response
        ];
    }
    
    return $decoded;
}