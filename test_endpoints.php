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

echo "=== TESTING V5 AUTH ENDPOINTS ===" . PHP_EOL . PHP_EOL;

// 1. Test check-user with multi-school user
echo "1. Testing check-user with multi-school user..." . PHP_EOL;
$result = makeRequest($baseUrl . '/check-user', [
    'email' => 'multi@admin-test-v5.com',
    'password' => 'multi123',
    'remember_me' => false
]);

echo "HTTP Code: " . $result['http_code'] . PHP_EOL;
if (isset($result['error'])) {
    echo "Error: " . $result['error'] . PHP_EOL;
} else {
    echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . PHP_EOL;
}

echo PHP_EOL . "---" . PHP_EOL . PHP_EOL;

// 2. Test check-user with single-school user
echo "2. Testing check-user with single-school user..." . PHP_EOL;
$result2 = makeRequest($baseUrl . '/check-user', [
    'email' => 'admin@escuela-test-v5.com',
    'password' => 'admin123',
    'remember_me' => false
]);

echo "HTTP Code: " . $result2['http_code'] . PHP_EOL;
if (isset($result2['error'])) {
    echo "Error: " . $result2['error'] . PHP_EOL;
} else {
    echo "Response: " . json_encode($result2['response'], JSON_PRETTY_PRINT) . PHP_EOL;
}

echo PHP_EOL . "---" . PHP_EOL . PHP_EOL;

// 3. If we got a temp token from the first test, test select-school
if (!isset($result['error']) && isset($result['response']['temp_token'])) {
    echo "3. Testing select-school with temp token..." . PHP_EOL;
    $tempToken = $result['response']['temp_token'];
    
    $result3 = makeRequest($baseUrl . '/select-school', [
        'school_id' => 2, // ESS Veveyse
        'remember_me' => false
    ], $tempToken);
    
    echo "HTTP Code: " . $result3['http_code'] . PHP_EOL;
    if (isset($result3['error'])) {
        echo "Error: " . $result3['error'] . PHP_EOL;
    } else {
        echo "Response: " . json_encode($result3['response'], JSON_PRETTY_PRINT) . PHP_EOL;
    }
}

echo PHP_EOL . "=== TEST COMPLETED ===" . PHP_EOL;