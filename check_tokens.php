<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Laravel\Sanctum\PersonalAccessToken;

echo "=== CHECKING TOKENS WITH CONTEXT_DATA ===" . PHP_EOL . PHP_EOL;

// Get tokens with context_data
$tokens = PersonalAccessToken::whereNotNull('context_data')
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get(['id', 'tokenable_id', 'name', 'context_data', 'created_at']);

if ($tokens->count() > 0) {
    echo "Found {$tokens->count()} tokens with context_data:" . PHP_EOL;
    foreach ($tokens as $token) {
        echo "ID: {$token->id}, User: {$token->tokenable_id}, Name: {$token->name}" . PHP_EOL;
        echo "Context: " . $token->context_data . PHP_EOL;
        echo "Created: {$token->created_at}" . PHP_EOL . PHP_EOL;
    }
} else {
    echo "No tokens with context_data found." . PHP_EOL;
}

echo "=== CHECKING ALL RECENT TOKENS ===" . PHP_EOL . PHP_EOL;

// Get all recent tokens
$allTokens = PersonalAccessToken::orderBy('created_at', 'desc')
    ->limit(5)
    ->get(['id', 'tokenable_id', 'name', 'context_data', 'created_at']);

foreach ($allTokens as $token) {
    echo "ID: {$token->id}, User: {$token->tokenable_id}, Name: {$token->name}" . PHP_EOL;
    echo "Context: " . ($token->context_data ?? 'NULL') . PHP_EOL;
    echo "Created: {$token->created_at}" . PHP_EOL . PHP_EOL;
}