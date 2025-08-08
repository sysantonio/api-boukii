<?php

$baseUrl = 'http://localhost/api-boukii/public/api/v5/auth';

function makeRequest($url, $data = null, $token = null) {
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
        ] + ($token ? ['Authorization: Bearer ' . $token] : []),
    ]);
    
    if ($data) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['error' => $error, 'http_code' => $httpCode];
    }
    
    return [
        'http_code' => $httpCode,
        'response' => json_decode($response, true),
        'raw_response' => $response
    ];
}

echo "=== TESTING SELECT-SCHOOL FLOW ===" . PHP_EOL . PHP_EOL;

// 1. Get temp token first
echo "1. Getting temp token..." . PHP_EOL;
$result = makeRequest($baseUrl . '/check-user', [
    'email' => 'multi@admin-test-v5.com',
    'password' => 'multi123',
    'remember_me' => false
]);

if (isset($result['error'])) {
    echo "Error: " . $result['error'] . PHP_EOL;
    exit;
}

if (!isset($result['response']['data']['temp_token'])) {
    echo "No temp token in response!" . PHP_EOL;
    echo "Full response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . PHP_EOL;
    exit;
}

$tempToken = $result['response']['data']['temp_token'];
echo "✅ Got temp token: " . substr($tempToken, 0, 20) . "..." . PHP_EOL . PHP_EOL;

// 2. Test select-school with ESS Veveyse (school_id = 2)
echo "2. Testing select-school with ESS Veveyse (ID: 2)..." . PHP_EOL;
$result2 = makeRequest($baseUrl . '/select-school', [
    'school_id' => 2,
    'remember_me' => false
], $tempToken);

echo "HTTP Code: " . $result2['http_code'] . PHP_EOL;
if (isset($result2['error'])) {
    echo "Error: " . $result2['error'] . PHP_EOL;
} else {
    echo "Response: " . json_encode($result2['response'], JSON_PRETTY_PRINT) . PHP_EOL;
    
    // If we got a valid token, test it
    if (isset($result2['response']['data']['access_token'])) {
        $finalToken = $result2['response']['data']['access_token'];
        echo PHP_EOL . "3. Testing final token with /me endpoint..." . PHP_EOL;
        
        $result3 = makeRequest($baseUrl . '/me', null, $finalToken);
        echo "HTTP Code: " . $result3['http_code'] . PHP_EOL;
        echo "Response: " . json_encode($result3['response'], JSON_PRETTY_PRINT) . PHP_EOL;
    }
}

echo PHP_EOL . "=== TEST COMPLETED ===" . PHP_EOL;