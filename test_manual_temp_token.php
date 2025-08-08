<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "=== TESTING MANUAL TEMP TOKEN CREATION ===" . PHP_EOL . PHP_EOL;

$user = User::where('email', 'multi@admin-test-v5.com')->first();

if (!$user) {
    echo "❌ User not found!" . PHP_EOL;
    exit;
}

// Create token the same way as in createTempUserToken
$tokenName = "manual_temp_test_" . time();
$expiresAt = now()->addMinutes(30);

echo "Creating token: {$tokenName}" . PHP_EOL;
echo "Expires at: {$expiresAt}" . PHP_EOL;

$token = $user->createToken($tokenName, [], $expiresAt);

echo "✅ Token created successfully" . PHP_EOL;
echo "Token ID: {$token->accessToken->id}" . PHP_EOL;
echo "Plain text token: {$token->plainTextToken}" . PHP_EOL;

// Update with context data
$contextJson = json_encode([
    'is_temp_user' => true,
    'created_at' => now()->toISOString(),
    'expires_at' => $expiresAt->toISOString(),
]);

$updateResult = $token->accessToken->update([
    'school_id' => null,
    'season_id' => null,
    'context_data' => $contextJson
]);

echo "Context update result: " . ($updateResult ? 'SUCCESS' : 'FAILED') . PHP_EOL;

// Test if the token can be found
$foundToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token->plainTextToken);
if ($foundToken) {
    echo "✅ Token can be found by Sanctum" . PHP_EOL;
} else {
    echo "❌ Token CANNOT be found by Sanctum" . PHP_EOL;
}

// Test HTTP request
echo PHP_EOL . "Testing HTTP request..." . PHP_EOL;

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://localhost/api-boukii/public/api/v5/auth/debug-token',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $token->plainTextToken
    ],
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: {$httpCode}" . PHP_EOL;
echo "Response: {$response}" . PHP_EOL;