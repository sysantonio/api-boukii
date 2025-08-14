<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\V5\Models\UserSeasonRole;
use App\Models\User;

echo "=== CHECKING USER SEASON ROLES ===" . PHP_EOL . PHP_EOL;

// Check if the model exists and table has data
try {
    $records = UserSeasonRole::all();
    echo "Found {$records->count()} UserSeasonRole records:" . PHP_EOL;
    
    foreach ($records as $record) {
        echo "User: {$record->user_id}, Season: {$record->season_id}, Role: {$record->role}" . PHP_EOL;
    }
    
    echo PHP_EOL . "=== CHECKING OUR TEST USERS ===" . PHP_EOL . PHP_EOL;
    
    // Check if our test users have season roles
    $user = User::where('email', 'multi@admin-test-v5.com')->first();
    if ($user) {
        echo "Multi-school user (ID: {$user->id}):" . PHP_EOL;
        $userRoles = UserSeasonRole::where('user_id', $user->id)->get();
        if ($userRoles->count() > 0) {
            foreach ($userRoles as $role) {
                echo "  - Season {$role->season_id}: {$role->role}" . PHP_EOL;
            }
        } else {
            echo "  - No season roles found!" . PHP_EOL;
        }
    }
    
    echo PHP_EOL;
    
    $singleUser = User::where('email', 'admin@escuela-test-v5.com')->first();
    if ($singleUser) {
        echo "Single-school user (ID: {$singleUser->id}):" . PHP_EOL;
        $userRoles = UserSeasonRole::where('user_id', $singleUser->id)->get();
        if ($userRoles->count() > 0) {
            foreach ($userRoles as $role) {
                echo "  - Season {$role->season_id}: {$role->role}" . PHP_EOL;
            }
        } else {
            echo "  - No season roles found!" . PHP_EOL;
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    echo "This might mean the UserSeasonRole table doesn't exist." . PHP_EOL;
}