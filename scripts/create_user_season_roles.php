<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\V5\Models\UserSeasonRole;
use App\Models\User;
use App\Models\Season;

echo "=== CREATING USER SEASON ROLES ===" . PHP_EOL . PHP_EOL;

// Get the multi-school user
$user = User::where('email', 'multi@admin-test-v5.com')->first();

if (!$user) {
    echo "❌ Multi-school user not found!" . PHP_EOL;
    exit;
}

echo "✅ Found multi-school user: {$user->email} (ID: {$user->id})" . PHP_EOL;

// Get all seasons for schools this user has access to
$seasons = Season::whereIn('school_id', [1, 2, 3])->get();

echo "✅ Found {$seasons->count()} seasons for user schools:" . PHP_EOL;
foreach ($seasons as $season) {
    echo "  - Season {$season->id}: {$season->name} (School {$season->school_id})" . PHP_EOL;
}

echo PHP_EOL . "Creating UserSeasonRole records..." . PHP_EOL;

foreach ($seasons as $season) {
    $existing = UserSeasonRole::where('user_id', $user->id)
        ->where('season_id', $season->id)
        ->first();
    
    if (!$existing) {
        UserSeasonRole::create([
            'user_id' => $user->id,
            'season_id' => $season->id,
            'role' => 'school_admin',
            'status' => 'active'
        ]);
        echo "✅ Created role for season {$season->id}: school_admin" . PHP_EOL;
    } else {
        echo "⚠️  Role already exists for season {$season->id}: {$existing->role}" . PHP_EOL;
    }
}

echo PHP_EOL . "=== VERIFICATION ===" . PHP_EOL;

// Verify the records were created
$userRoles = UserSeasonRole::where('user_id', $user->id)->get();
echo "User now has {$userRoles->count()} season roles:" . PHP_EOL;
foreach ($userRoles as $role) {
    echo "  - Season {$role->season_id}: {$role->role} ({$role->status})" . PHP_EOL;
}

echo PHP_EOL . "✅ User season roles created successfully!" . PHP_EOL;