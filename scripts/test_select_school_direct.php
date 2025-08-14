<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "=== DIRECT SELECT-SCHOOL TEST ===" . PHP_EOL . PHP_EOL;

// Find the multi-school user and create a temp token
$user = User::where('email', 'multi@admin-test-v5.com')->first();

if (!$user) {
    echo "❌ User not found!" . PHP_EOL;
    exit;
}

// Create a temp token manually 
$tokenName = "temp_select_test_" . time();
$token = $user->createToken($tokenName, [], now()->addHours(1));

// Mark it as temp token
$contextJson = json_encode([
    'is_temp_user' => true,
    'created_at' => now()->toISOString(),
    'expires_at' => now()->addHours(1)->toISOString(),
]);

$token->accessToken->update([
    'school_id' => null,
    'season_id' => null,
    'context_data' => $contextJson
]);

echo "✅ Created temp token: " . substr($token->plainTextToken, 0, 20) . "..." . PHP_EOL . PHP_EOL;

// Test select-school endpoint
echo "Testing select-school endpoint..." . PHP_EOL;

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://localhost/api-boukii/public/api/v5/auth/select-school',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $token->plainTextToken
    ],
    CURLOPT_POSTFIELDS => json_encode([
        'school_id' => 2, // ESS Veveyse
        'remember_me' => false
    ]),
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: {$httpCode}" . PHP_EOL;
echo "Response: " . PHP_EOL;
echo $response . PHP_EOL;

if ($httpCode === 200) {
    $responseData = json_decode($response, true);
    if (isset($responseData['data']['access_token'])) {
        $finalToken = $responseData['data']['access_token'];
        echo PHP_EOL . "✅ Got final token, testing /me endpoint..." . PHP_EOL;
        
        $ch2 = curl_init();
        curl_setopt_array($ch2, [
            CURLOPT_URL => 'http://localhost/api-boukii/public/api/v5/auth/me',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Bearer ' . $finalToken
            ],
        ]);
        
        $response2 = curl_exec($ch2);
        $httpCode2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
        curl_close($ch2);
        
        echo "Final token /me HTTP Code: {$httpCode2}" . PHP_EOL;
        echo "Final token /me Response: " . PHP_EOL;
        echo $response2 . PHP_EOL;
    }
}