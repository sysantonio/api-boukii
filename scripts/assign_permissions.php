<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "🔄 Asignando permisos a usuarios...\n";

// Find users
$adminUser = User::where('email', 'admin@escuela-test-v5.com')->first();
$multiUser = User::where('email', 'multi@admin-test-v5.com')->first();

$seasons = [11, 12, 13]; // Add more season IDs as needed

if ($adminUser) {
    foreach ($seasons as $seasonId) {
        DB::table('user_season_roles')->updateOrInsert(
            ['user_id' => $adminUser->id, 'season_id' => $seasonId],
            [
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
    }
    echo "✅ Permisos dados a {$adminUser->email} para temporadas: " . implode(', ', $seasons) . "\n";
} else {
    echo "❌ Usuario admin@escuela-test-v5.com no encontrado\n";
}

if ($multiUser) {
    foreach ($seasons as $seasonId) {
        DB::table('user_season_roles')->updateOrInsert(
            ['user_id' => $multiUser->id, 'season_id' => $seasonId],
            [
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
    }
    echo "✅ Permisos dados a {$multiUser->email} para temporadas: " . implode(', ', $seasons) . "\n";
} else {
    echo "❌ Usuario multi@admin-test-v5.com no encontrado\n";
}

// Verify permissions
echo "\n🔍 Verificando permisos creados:\n";
$permissions = DB::table('user_season_roles')
    ->join('users', 'user_season_roles.user_id', '=', 'users.id')
    ->select('users.email', 'user_season_roles.season_id', 'user_season_roles.role')
    ->whereIn('users.email', ['admin@escuela-test-v5.com', 'multi@admin-test-v5.com'])
    ->get();

foreach ($permissions as $permission) {
    echo "👤 {$permission->email} -> Temporada {$permission->season_id} -> Rol: {$permission->role}\n";
}

echo "\n✅ Proceso completado!\n";