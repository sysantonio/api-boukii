<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Laravel\Sanctum\PersonalAccessToken;

echo "=== DEBUGGING TOKEN FORMAT ===" . PHP_EOL . PHP_EOL;

// Get the latest token
$latestToken = PersonalAccessToken::orderBy('created_at', 'desc')->first();

if ($latestToken) {
    echo "Latest token details:" . PHP_EOL;
    echo "ID: {$latestToken->id}" . PHP_EOL;
    echo "Tokenable ID: {$latestToken->tokenable_id}" . PHP_EOL;
    echo "Name: {$latestToken->name}" . PHP_EOL;
    echo "Token (hash): " . substr($latestToken->token, 0, 20) . "..." . PHP_EOL;
    echo "Abilities: " . json_encode($latestToken->abilities) . PHP_EOL;
    echo "Context data: " . ($latestToken->context_data ?? 'NULL') . PHP_EOL;
    echo "Created: {$latestToken->created_at}" . PHP_EOL . PHP_EOL;
    
    // Let's see the full token format expected
    $fullToken = $latestToken->id . '|' . 'test-plain-text-token';
    echo "Expected full token format: {$fullToken}" . PHP_EOL . PHP_EOL;
    
    // Test token verification manually
    echo "=== MANUAL TOKEN VERIFICATION ===" . PHP_EOL;
    
    // Try to find the token using Sanctum's method
    $testTokenPlain = '4768|NAzIJ44s2407xccqvfVRXJLqc4YJ1o9nJ29d7k6dab3fbc68';
    echo "Testing token: " . substr($testTokenPlain, 0, 30) . "..." . PHP_EOL;
    
    $foundToken = PersonalAccessToken::findToken($testTokenPlain);
    if ($foundToken) {
        echo "✅ Token found by Sanctum's findToken method" . PHP_EOL;
        echo "Token ID: {$foundToken->id}" . PHP_EOL;
        echo "User ID: {$foundToken->tokenable_id}" . PHP_EOL;
    } else {
        echo "❌ Token NOT found by Sanctum's findToken method" . PHP_EOL;
        
        // Let's check if the hash matches
        [$id, $token] = explode('|', $testTokenPlain, 2);
        $hashedToken = hash('sha256', $token);
        echo "Token ID from string: {$id}" . PHP_EOL;
        echo "Hashed token: " . substr($hashedToken, 0, 20) . "..." . PHP_EOL;
        
        $dbToken = PersonalAccessToken::find($id);
        if ($dbToken) {
            echo "DB token hash: " . substr($dbToken->token, 0, 20) . "..." . PHP_EOL;
            echo "Hashes match: " . (hash_equals($dbToken->token, $hashedToken) ? 'YES' : 'NO') . PHP_EOL;
        }
    }
} else {
    echo "No tokens found in database." . PHP_EOL;
}