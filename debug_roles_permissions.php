<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

echo "=== DEBUGGING ROLES AND PERMISSIONS ===" . PHP_EOL . PHP_EOL;

// Check all roles and their permissions
echo "1. All roles and their permissions:" . PHP_EOL;
$roles = Role::with('permissions')->get();

foreach ($roles as $role) {
    echo "Role: {$role->name}" . PHP_EOL;
    if ($role->permissions->count() > 0) {
        foreach ($role->permissions as $permission) {
            echo "  - {$permission->name}" . PHP_EOL;
        }
    } else {
        echo "  (no permissions)" . PHP_EOL;
    }
    echo PHP_EOL;
}

echo "2. All permissions:" . PHP_EOL;
$permissions = Permission::all();
foreach ($permissions as $permission) {
    echo "- {$permission->name}" . PHP_EOL;
}

echo PHP_EOL . "3. Creating basic permissions for school_admin if needed..." . PHP_EOL;

$schoolAdminRole = Role::where('name', 'school_admin')->first();
if ($schoolAdminRole) {
    // Basic permissions that a school admin should have
    $basicPermissions = [
        'view_dashboard',
        'manage_users',
        'manage_courses',
        'manage_bookings',
        'view_analytics'
    ];
    
    foreach ($basicPermissions as $permName) {
        $permission = Permission::firstOrCreate(['name' => $permName]);
        
        if (!$schoolAdminRole->hasPermissionTo($permName)) {
            $schoolAdminRole->givePermissionTo($permName);
            echo "✅ Added permission '{$permName}' to school_admin" . PHP_EOL;
        } else {
            echo "⚠️  Permission '{$permName}' already exists for school_admin" . PHP_EOL;
        }
    }
    
    echo PHP_EOL . "Updated school_admin permissions:" . PHP_EOL;
    $schoolAdminRole->refresh();
    foreach ($schoolAdminRole->permissions as $permission) {
        echo "  - {$permission->name}" . PHP_EOL;
    }
} else {
    echo "❌ school_admin role not found!" . PHP_EOL;
}