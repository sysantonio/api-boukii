<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "🔍 DEBUGGING FORBIDDEN ERROR FOR SEASON ID 11\n";
echo "=============================================\n\n";

$users = ['admin@escuela-test-v5.com', 'multi@admin-test-v5.com'];

foreach ($users as $email) {
    echo "👤 CHECKING USER: {$email}\n";
    
    $user = User::where('email', $email)->first();
    
    if (!$user) {
        echo "❌ USER NOT FOUND\n\n";
        continue;
    }
    
    echo "✅ User found - ID: {$user->id}\n";
    
    // Check all permissions for this user
    echo "📋 ALL PERMISSIONS:\n";
    $allPermissions = DB::table('user_season_roles')
        ->where('user_id', $user->id)
        ->select('season_id', 'role', 'created_at', 'updated_at')
        ->orderBy('season_id')
        ->get();
    
    if ($allPermissions->count() == 0) {
        echo "❌ NO PERMISSIONS FOUND\n";
    } else {
        foreach ($allPermissions as $perm) {
            $status = $perm->season_id == 11 ? '🎯 TARGET' : '  ';
            echo "{$status} Season {$perm->season_id}: {$perm->role} (created: {$perm->created_at})\n";
        }
    }
    
    // Check specifically for season 11
    echo "\n🎯 SPECIFIC CHECK FOR SEASON 11:\n";
    $season11Permission = DB::table('user_season_roles')
        ->where('user_id', $user->id)
        ->where('season_id', 11)
        ->first();
    
    if ($season11Permission) {
        echo "✅ HAS PERMISSION FOR SEASON 11\n";
        echo "   Role: {$season11Permission->role}\n";
        echo "   Created: {$season11Permission->created_at}\n";
        echo "   Updated: {$season11Permission->updated_at}\n";
    } else {
        echo "❌ NO PERMISSION FOR SEASON 11\n";
        
        // Let's create it right now
        echo "🔧 CREATING PERMISSION FOR SEASON 11...\n";
        DB::table('user_season_roles')->insert([
            'user_id' => $user->id,
            'season_id' => 11,
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        echo "✅ PERMISSION CREATED\n";
    }
    
    echo "\n" . str_repeat("-", 50) . "\n\n";
}

// Let's also check if season 11 exists
echo "🔍 CHECKING SEASON 11 EXISTS:\n";
$season11 = DB::table('seasons')->where('id', 11)->first();
if ($season11) {
    echo "✅ Season 11 exists: {$season11->name}\n";
} else {
    echo "❌ Season 11 does not exist!\n";
}

echo "\n✅ DEBUGGING COMPLETE\n";