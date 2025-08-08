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

echo "=== COMPLETE SCHOOL SELECTION FLOW TEST ===" . PHP_EOL . PHP_EOL;

echo "🔍 STEP 1: Check multi-school user credentials..." . PHP_EOL;
$result1 = makeRequest($baseUrl . '/check-user', [
    'email' => 'multi@admin-test-v5.com',
    'password' => 'multi123',
    'remember_me' => false
]);

if ($result1['http_code'] !== 200) {
    echo "❌ STEP 1 FAILED: HTTP {$result1['http_code']}" . PHP_EOL;
    echo json_encode($result1['response'], JSON_PRETTY_PRINT) . PHP_EOL;
    exit;
}

$data1 = $result1['response']['data'];
if (!$data1['requires_school_selection']) {
    echo "❌ STEP 1 FAILED: Expected requires_school_selection = true" . PHP_EOL;
    exit;
}

echo "✅ STEP 1 SUCCESS: Found {$data1['user']['email']} with " . count($data1['available_schools']) . " schools" . PHP_EOL;
foreach ($data1['available_schools'] as $school) {
    echo "   - {$school['name']} (ID: {$school['id']})" . PHP_EOL;
}

$tempToken = $data1['temp_token'];
echo "   Temp token: " . substr($tempToken, 0, 20) . "..." . PHP_EOL . PHP_EOL;

echo "🏫 STEP 2: Select ESS Veveyse (ID: 2)..." . PHP_EOL;
$result2 = makeRequest($baseUrl . '/select-school', [
    'school_id' => 2,
    'remember_me' => false
], $tempToken);

if ($result2['http_code'] !== 200) {
    echo "❌ STEP 2 FAILED: HTTP {$result2['http_code']}" . PHP_EOL;
    echo json_encode($result2['response'], JSON_PRETTY_PRINT) . PHP_EOL;
    exit;
}

$data2 = $result2['response']['data'];
echo "✅ STEP 2 SUCCESS: Login completed for {$data2['school']['name']}" . PHP_EOL;
echo "   Season: {$data2['season']['name']}" . PHP_EOL;
echo "   Permissions: " . implode(', ', $data2['user']['permissions']) . PHP_EOL;

$finalToken = $data2['access_token'];
echo "   Final token: " . substr($finalToken, 0, 20) . "..." . PHP_EOL . PHP_EOL;

echo "👤 STEP 3: Test /me endpoint with final token..." . PHP_EOL;
$result3 = makeRequest($baseUrl . '/me', null, $finalToken);

if ($result3['http_code'] !== 200) {
    echo "❌ STEP 3 FAILED: HTTP {$result3['http_code']}" . PHP_EOL;
    echo json_encode($result3['response'], JSON_PRETTY_PRINT) . PHP_EOL;
    exit;
}

$data3 = $result3['response'];
echo "✅ STEP 3 SUCCESS: /me endpoint working correctly" . PHP_EOL;
echo "   User: {$data3['email']}" . PHP_EOL;
echo "   School: {$data3['school']['name']} (ID: {$data3['school_id']})" . PHP_EOL;
echo "   Authenticated: " . ($data3['authenticated'] ? 'YES' : 'NO') . PHP_EOL;
echo "   Token valid: " . ($data3['token_valid'] ? 'YES' : 'NO') . PHP_EOL . PHP_EOL;

echo "🔄 STEP 4: Test single-school user (auto-login)..." . PHP_EOL;
$result4 = makeRequest($baseUrl . '/check-user', [
    'email' => 'admin@escuela-test-v5.com',
    'password' => 'admin123',
    'remember_me' => false
]);

if ($result4['http_code'] !== 200) {
    echo "❌ STEP 4 FAILED: HTTP {$result4['http_code']}" . PHP_EOL;
    exit;
}

$data4 = $result4['response']['data'];
if (isset($data4['requires_school_selection'])) {
    echo "❌ STEP 4 FAILED: Single-school user should not require school selection" . PHP_EOL;
    exit;
}

echo "✅ STEP 4 SUCCESS: Single-school user auto-logged in" . PHP_EOL;
echo "   User: {$data4['user']['email']}" . PHP_EOL;
echo "   School: {$data4['school']['name']}" . PHP_EOL;
echo "   Season: {$data4['season']['name']}" . PHP_EOL . PHP_EOL;

echo "🎉 ALL TESTS PASSED! Complete school selection flow is working correctly!" . PHP_EOL;
echo "✅ Multi-school users get school selection interface" . PHP_EOL;
echo "✅ Single-school users get automatic login" . PHP_EOL;
echo "✅ School selection completes login process" . PHP_EOL;
echo "✅ Final tokens work with authenticated endpoints" . PHP_EOL;