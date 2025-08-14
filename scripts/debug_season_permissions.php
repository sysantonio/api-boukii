<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\V5\Modules\Auth\Services\AuthV5Service;
use App\V5\Models\UserSeasonRole;
use App\Models\User;
use Spatie\Permission\Models\Role;

echo "=== DEBUGGING SEASON PERMISSIONS ===" . PHP_EOL . PHP_EOL;

$userId = 20207; // Multi-school user
$seasonId = 13;  // The season from our token

echo "Checking permissions for User {$userId}, Season {$seasonId}" . PHP_EOL . PHP_EOL;

// Check UserSeasonRole record
echo "1. Checking UserSeasonRole record..." . PHP_EOL;
$userSeasonRole = UserSeasonRole::where('user_id', $userId)
    ->where('season_id', $seasonId)
    ->first();

if ($userSeasonRole) {
    echo "✅ Found UserSeasonRole: {$userSeasonRole->role}" . PHP_EOL;
    
    // Check if the role exists in Spatie roles
    echo "2. Checking Spatie role..." . PHP_EOL;
    $role = Role::where('name', $userSeasonRole->role)->first();
    
    if ($role) {
        echo "✅ Found Spatie role: {$role->name}" . PHP_EOL;
        echo "   Permissions: " . $role->permissions->pluck('name')->implode(', ') . PHP_EOL;
    } else {
        echo "❌ Spatie role '{$userSeasonRole->role}' not found!" . PHP_EOL;
    }
} else {
    echo "❌ No UserSeasonRole found!" . PHP_EOL;
}

echo PHP_EOL . "3. Testing AuthV5Service::checkSeasonPermissions..." . PHP_EOL;

try {
    $authService = new AuthV5Service();
    $permissions = $authService->checkSeasonPermissions($userId, $seasonId);
    
    echo "✅ Permissions returned: " . PHP_EOL;
    if (empty($permissions)) {
        echo "   (empty array)" . PHP_EOL;
    } else {
        foreach ($permissions as $permission) {
            echo "   - {$permission}" . PHP_EOL;
        }
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "4. Checking all Spatie roles..." . PHP_EOL;
$allRoles = Role::all();
foreach ($allRoles as $role) {
    echo "- {$role->name}" . PHP_EOL;
}