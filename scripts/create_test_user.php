<?php

require_once 'bootstrap/app.php';

use App\Models\User;
use App\Models\School;
use Illuminate\Support\Facades\Hash;

// Create or update test user
$user = User::updateOrCreate(
    ['email' => 'test@v5.local'],
    [
        'name' => 'Test User V5',
        'email' => 'test@v5.local',
        'password' => Hash::make('password123'),
        'active' => true,
        'created_at' => now(),
        'updated_at' => now()
    ]
);

// Associate with school 2
$user->schools()->syncWithoutDetaching([2 => ['created_at' => now(), 'updated_at' => now()]]);

echo "User created/updated: {$user->email} (ID: {$user->id})\n";
echo "Associated with school ID: 2\n";
echo "Password: password123\n";
