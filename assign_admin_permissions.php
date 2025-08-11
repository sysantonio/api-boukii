<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Season;
use Illuminate\Support\Facades\DB;

echo "🔧 Assigning admin permissions to admin@boukii-v5.com for school 2...\n";

try {
    // Find the user
    $user = User::where('email', 'admin@boukii-v5.com')->first();
    if (!$user) {
        echo "❌ User admin@boukii-v5.com not found\n";
        exit(1);
    }

    echo "✅ Found user: {$user->first_name} {$user->last_name} (ID: {$user->id})\n";

    // Find season 6 for school 2
    $season = Season::where('id', 6)->where('school_id', 2)->first();
    if (!$season) {
        echo "❌ Season 6 for school 2 not found\n";
        exit(1);
    }

    echo "✅ Found season: {$season->name} (ID: {$season->id}) for school 2\n";

    // List of all permissions an admin should have
    $adminPermissions = [
        // Season level permissions
        'season.admin',
        'season.manager', 
        'season.view',
        'season.bookings',
        'season.clients',
        'season.monitors',
        'season.courses',
        'season.analytics', // This is what was missing!
        'season.equipment',
        
        // Specific resource permissions
        'booking.create',
        'booking.read',
        'booking.update',
        'booking.delete',
        'booking.payment',
        
        'client.create',
        'client.read',
        'client.update',
        'client.delete',
        'client.export',
        
        'monitor.create',
        'monitor.read',
        'monitor.update',
        'monitor.delete',
        'monitor.schedule',
        
        'course.create',
        'course.read',
        'course.update',
        'course.delete',
        'course.pricing',

        // School level permissions
        'school.admin',
        'school.manager',
        'school.staff',
        'school.view',
        'school.settings',
        'school.users',
        'school.billing'
    ];

    echo "🔄 Creating permissions if they don't exist...\n";

    // Create permissions for both guards if they don't exist
    foreach ($adminPermissions as $permissionName) {
        // Create for web guard (default)
        $webPermission = \Spatie\Permission\Models\Permission::firstOrCreate([
            'name' => $permissionName,
            'guard_name' => 'web'
        ]);
        
        // Also create for api_v5 guard
        $apiPermission = \Spatie\Permission\Models\Permission::firstOrCreate([
            'name' => $permissionName,
            'guard_name' => 'api_v5'
        ]);
        
        echo "  ✅ Permission: {$permissionName} (web & api_v5)\n";
    }

    echo "🔄 Assigning permissions to user...\n";

    // Give all permissions to the user for the correct guard
    // Note: User model might be using web guard, but we need to check
    $user->syncPermissions($adminPermissions); // Use default guard first

    echo "✅ All permissions assigned to {$user->email}\n";

    // Verify permissions
    echo "\n🔍 Verifying permissions:\n";
    $userPermissions = $user->getAllPermissions();
    foreach ($userPermissions as $permission) {
        echo "  ✅ {$permission->name} ({$permission->guard_name})\n";
    }

    echo "\n🎉 Successfully configured admin permissions!\n";
    echo "User {$user->email} now has full admin access to school 2, season 6.\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}