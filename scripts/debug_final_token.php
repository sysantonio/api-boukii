<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Laravel\Sanctum\PersonalAccessToken;

echo "=== DEBUGGING FINAL TOKEN ===" . PHP_EOL . PHP_EOL;

// Check the latest token (should be the final token from select-school)
$latestToken = PersonalAccessToken::orderBy('created_at', 'desc')->first();

if ($latestToken) {
    echo "Latest token details:" . PHP_EOL;
    echo "ID: {$latestToken->id}" . PHP_EOL;
    echo "User ID: {$latestToken->tokenable_id}" . PHP_EOL;
    echo "Name: {$latestToken->name}" . PHP_EOL;
    echo "School ID: " . ($latestToken->school_id ?? 'NULL') . PHP_EOL;
    echo "Season ID: " . ($latestToken->season_id ?? 'NULL') . PHP_EOL;
    echo "Context data: " . ($latestToken->context_data ?? 'NULL') . PHP_EOL;
    echo "Abilities: " . json_encode($latestToken->abilities) . PHP_EOL;
    echo "Expires at: " . ($latestToken->expires_at ?? 'NULL') . PHP_EOL;
    echo "Created: {$latestToken->created_at}" . PHP_EOL . PHP_EOL;
    
    // Test findToken with the actual token from response
    $testToken = '4771|ZVo5gwv18nvQYMUxixfNiRjgN20lAkvZlPZ4ycOadad2e5fc';
    echo "Testing token: " . substr($testToken, 0, 30) . "..." . PHP_EOL;
    
    $foundToken = PersonalAccessToken::findToken($testToken);
    if ($foundToken) {
        echo "✅ Token found by findToken" . PHP_EOL;
        echo "Found token ID: {$foundToken->id}" . PHP_EOL;
        echo "Found school_id: " . ($foundToken->school_id ?? 'NULL') . PHP_EOL;
        echo "Found season_id: " . ($foundToken->season_id ?? 'NULL') . PHP_EOL;
        echo "Found context_data: " . ($foundToken->context_data ?? 'NULL') . PHP_EOL;
    } else {
        echo "❌ Token NOT found by findToken" . PHP_EOL;
    }
} else {
    echo "No tokens found." . PHP_EOL;
}