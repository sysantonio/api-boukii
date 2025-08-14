<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Laravel\Sanctum\PersonalAccessToken;

echo "=== DEBUGGING TEMP TOKEN ===" . PHP_EOL . PHP_EOL;

// Look for the most recent temp token
$testToken = '4772|S2UWMVhv7PTtM0ZEKjbwROAZkjXPKJgLKPUgHWWOe32b49b7';

echo "Testing token: " . substr($testToken, 0, 30) . "..." . PHP_EOL;

// Try to find it using Sanctum's method
$foundToken = PersonalAccessToken::findToken($testToken);

if ($foundToken) {
    echo "✅ Token found by Sanctum findToken" . PHP_EOL;
    echo "Token ID: {$foundToken->id}" . PHP_EOL;
    echo "User ID: {$foundToken->tokenable_id}" . PHP_EOL;
    echo "Name: {$foundToken->name}" . PHP_EOL;
    echo "School ID: " . ($foundToken->school_id ?? 'NULL') . PHP_EOL;
    echo "Season ID: " . ($foundToken->season_id ?? 'NULL') . PHP_EOL;
    echo "Context data: " . ($foundToken->context_data ?? 'NULL') . PHP_EOL;
    echo "Abilities: " . json_encode($foundToken->abilities) . PHP_EOL;
    echo "Expires at: " . ($foundToken->expires_at ?? 'NULL') . PHP_EOL;
} else {
    echo "❌ Token NOT found by Sanctum findToken" . PHP_EOL;
    
    // Try to find by ID manually
    [$id, $tokenPart] = explode('|', $testToken, 2);
    echo "Token ID from string: {$id}" . PHP_EOL;
    
    $dbToken = PersonalAccessToken::find($id);
    if ($dbToken) {
        echo "✅ Found token in DB by ID" . PHP_EOL;
        echo "DB Token hash: " . substr($dbToken->token, 0, 20) . "..." . PHP_EOL;
        
        // Check hash manually
        $expectedHash = hash('sha256', $tokenPart);
        echo "Expected hash: " . substr($expectedHash, 0, 20) . "..." . PHP_EOL;
        echo "Hashes match: " . (hash_equals($dbToken->token, $expectedHash) ? 'YES' : 'NO') . PHP_EOL;
    } else {
        echo "❌ Token not found in DB by ID" . PHP_EOL;
    }
}

echo PHP_EOL . "=== CHECKING LATEST TOKENS ===" . PHP_EOL;
$latestTokens = PersonalAccessToken::orderBy('created_at', 'desc')->limit(3)->get();
foreach ($latestTokens as $token) {
    echo "ID: {$token->id}, Name: {$token->name}, User: {$token->tokenable_id}, Created: {$token->created_at}" . PHP_EOL;
}