<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Laravel\Sanctum\PersonalAccessToken;

echo "=== CHECKING TOKEN EXISTENCE ===" . PHP_EOL . PHP_EOL;

$tokenId = 4773;
echo "Checking token {$tokenId}:" . PHP_EOL;

$token = PersonalAccessToken::find($tokenId);

if ($token) {
    echo "✅ Token found!" . PHP_EOL;
    echo "Name: {$token->name}" . PHP_EOL;
    echo "User ID: {$token->tokenable_id}" . PHP_EOL;
    echo "Created: {$token->created_at}" . PHP_EOL;
    echo "Expires: " . ($token->expires_at ?? 'Never') . PHP_EOL;
    echo "Context: " . ($token->context_data ?? 'NULL') . PHP_EOL;
    
    // Check if expired
    if ($token->expires_at && $token->expires_at->isPast()) {
        echo "⚠️  Token is EXPIRED!" . PHP_EOL;
    } else {
        echo "✅ Token is still valid" . PHP_EOL;
    }
} else {
    echo "❌ Token not found or deleted" . PHP_EOL;
}

echo PHP_EOL . "=== LATEST TOKENS ===" . PHP_EOL;
$latestTokens = PersonalAccessToken::orderBy('created_at', 'desc')->limit(5)->get();
foreach ($latestTokens as $token) {
    $expired = $token->expires_at && $token->expires_at->isPast() ? ' (EXPIRED)' : '';
    echo "ID: {$token->id}, Name: {$token->name}, Created: {$token->created_at}{$expired}" . PHP_EOL;
}