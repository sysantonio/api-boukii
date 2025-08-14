<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

echo "=== MANUAL TOKEN CREATION TEST ===" . PHP_EOL . PHP_EOL;

// Find the multi-school user
$user = User::where('email', 'multi@admin-test-v5.com')->first();

if (!$user) {
    echo "❌ User not found!" . PHP_EOL;
    exit;
}

echo "✅ Found user: {$user->email} (ID: {$user->id})" . PHP_EOL;

// Create a token manually
$tokenName = "manual_test_token_" . time();
echo "Creating token: {$tokenName}" . PHP_EOL;

$token = $user->createToken($tokenName, [], now()->addHours(1));

echo "✅ Token created successfully" . PHP_EOL;
echo "Token ID: {$token->accessToken->id}" . PHP_EOL;
echo "Plain text token: {$token->plainTextToken}" . PHP_EOL . PHP_EOL;

// Test if we can find it back
$foundToken = PersonalAccessToken::findToken($token->plainTextToken);

if ($foundToken) {
    echo "✅ Token found by findToken method" . PHP_EOL;
    echo "Found token ID: {$foundToken->id}" . PHP_EOL;
    echo "User ID: {$foundToken->tokenable_id}" . PHP_EOL;
} else {
    echo "❌ Token NOT found by findToken method" . PHP_EOL;
}

echo PHP_EOL . "=== Testing HTTP request with manual token ===" . PHP_EOL;

// Test HTTP request
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://localhost/api-boukii/public/api/v5/auth/me',
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