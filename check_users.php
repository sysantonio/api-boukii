<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== USER TABLE STRUCTURE ===\n";
$user = App\Models\User::first();
if ($user) {
    echo "First user attributes:\n";
    foreach ($user->getAttributes() as $key => $value) {
        if (in_array($key, ['password', 'remember_token', 'password_hash'])) {
            continue; // Skip sensitive fields
        }
        $displayValue = is_string($value) ? substr($value, 0, 50) : $value;
        echo "  {$key}: {$displayValue}\n";
    }
} else {
    echo "No users found\n";
}

echo "\n=== SAMPLE USERS (first 3) ===\n";
$users = App\Models\User::take(3)->get();
foreach ($users as $user) {
    $name = $user->nombre ?? $user->name ?? 'No name field';
    $email = $user->email ?? 'No email';
    echo "ID: {$user->id} - {$name} ({$email})\n";
}