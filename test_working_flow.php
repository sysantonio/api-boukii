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

echo "=== TESTING WITH WORKING TEMP TOKEN ===" . PHP_EOL . PHP_EOL;

// Use the token we know works
$workingTempToken = '4773|MsxolxRelr5lb54TL551WjqhWuB1dDtw8GaDRavd46708a46';

echo "🏫 STEP 1: Select school with working temp token..." . PHP_EOL;
$result1 = makeRequest($baseUrl . '/select-school', [
    'school_id' => 1, // Try School Testing this time
    'remember_me' => false
], $workingTempToken);

if ($result1['http_code'] !== 200) {
    echo "❌ STEP 1 FAILED: HTTP {$result1['http_code']}" . PHP_EOL;
    echo json_encode($result1['response'], JSON_PRETTY_PRINT) . PHP_EOL;
    exit;
}

$data1 = $result1['response']['data'];
echo "✅ STEP 1 SUCCESS: Login completed for {$data1['school']['name']}" . PHP_EOL;
echo "   Season: {$data1['season']['name']}" . PHP_EOL;

$finalToken = $data1['access_token'];
echo "   Final token: " . substr($finalToken, 0, 20) . "..." . PHP_EOL . PHP_EOL;

echo "👤 STEP 2: Test /me endpoint with final token..." . PHP_EOL;
$result2 = makeRequest($baseUrl . '/me', null, $finalToken);

if ($result2['http_code'] !== 200) {
    echo "❌ STEP 2 FAILED: HTTP {$result2['http_code']}" . PHP_EOL;
    echo json_encode($result2['response'], JSON_PRETTY_PRINT) . PHP_EOL;
    exit;
}

$data2 = $result2['response'];
echo "✅ STEP 2 SUCCESS: /me endpoint working correctly" . PHP_EOL;
echo "   User: {$data2['email']}" . PHP_EOL;
echo "   School: {$data2['school']['name']} (ID: {$data2['school_id']})" . PHP_EOL;
echo "   Authenticated: " . ($data2['authenticated'] ? 'YES' : 'NO') . PHP_EOL . PHP_EOL;

echo "🎉 FLOW WORKS WITH MANUAL TEMP TOKEN!" . PHP_EOL;
echo "The issue is specifically with tokens created by check-user endpoint." . PHP_EOL;