<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\School;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

echo "🔧 Creating School Admin Role System...\n";

try {
    // Create 'school-admin' role if it doesn't exist
    $schoolAdminRole = Role::firstOrCreate([
        'name' => 'school-admin',
        'guard_name' => 'web'
    ]);

    echo "✅ School Admin Role created/found: {$schoolAdminRole->name}\n";

    // Get all permissions that a school admin should have
    $schoolAdminPermissions = [
        // Season level permissions
        'season.admin', 'season.manager', 'season.view', 'season.bookings',
        'season.clients', 'season.monitors', 'season.courses', 'season.analytics', 'season.equipment',
        
        // Specific resource permissions
        'booking.create', 'booking.read', 'booking.update', 'booking.delete', 'booking.payment',
        'client.create', 'client.read', 'client.update', 'client.delete', 'client.export',
        'monitor.create', 'monitor.read', 'monitor.update', 'monitor.delete', 'monitor.schedule',
        'course.create', 'course.read', 'course.update', 'course.delete', 'course.pricing',

        // School level permissions
        'school.admin', 'school.manager', 'school.staff', 'school.view',
        'school.settings', 'school.users', 'school.billing'
    ];

    // Assign all permissions to the school-admin role
    $schoolAdminRole->syncPermissions($schoolAdminPermissions);

    echo "✅ Assigned " . count($schoolAdminPermissions) . " permissions to school-admin role\n";

    // Assign the school-admin role to admin@boukii-v5.com
    $user = User::where('email', 'admin@boukii-v5.com')->first();
    
    if ($user) {
        $user->assignRole('school-admin');
        echo "✅ Assigned 'school-admin' role to {$user->email}\n";

        // Also ensure the user is connected to school 2
        DB::table('school_users')->updateOrInsert(
            ['user_id' => $user->id, 'school_id' => 2],
            [
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
        echo "✅ Ensured user is admin of school 2\n";

        // Verify permissions
        echo "\n🔍 Verifying user has all permissions:\n";
        $userPermissions = $user->getAllPermissions();
        echo "User now has " . $userPermissions->count() . " permissions:\n";
        
        $hasSeasonAnalytics = $user->hasPermissionTo('season.analytics');
        echo "  ✅ season.analytics: " . ($hasSeasonAnalytics ? 'YES' : 'NO') . "\n";
        
        $hasSchoolAdmin = $user->hasPermissionTo('school.admin');
        echo "  ✅ school.admin: " . ($hasSchoolAdmin ? 'YES' : 'NO') . "\n";
    } else {
        echo "❌ User admin@boukii-v5.com not found\n";
    }

    echo "\n🎉 School Admin Role System configured successfully!\n";
    echo "💡 To assign school admin to any user for any school:\n";
    echo "   1. Assign 'school-admin' role: \$user->assignRole('school-admin')\n";
    echo "   2. Connect to school: INSERT INTO school_users (user_id, school_id, role)\n";
    echo "   3. Add season role: INSERT INTO user_season_roles (user_id, season_id, role, is_active)\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}