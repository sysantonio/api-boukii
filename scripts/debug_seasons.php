<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Season;
use Illuminate\Support\Facades\DB;

echo "🔍 DEBUGGING SEASON ACCESS ISSUES\n";
echo "================================\n\n";

// 1. Check all existing seasons
echo "📅 TODAS LAS TEMPORADAS EN EL SISTEMA:\n";
$allSeasons = Season::select('id', 'name', 'start_date', 'end_date')->get();
foreach ($allSeasons as $season) {
    echo "ID: {$season->id} - {$season->name} ({$season->start_date} - {$season->end_date})\n";
}

echo "\n";

// 2. Check user permissions
$users = ['admin@escuela-test-v5.com', 'multi@admin-test-v5.com'];

foreach ($users as $email) {
    echo "👤 PERMISOS PARA {$email}:\n";
    $user = User::where('email', $email)->first();
    
    if ($user) {
        $permissions = DB::table('user_season_roles')
            ->where('user_id', $user->id)
            ->select('season_id', 'role')
            ->get();
            
        if ($permissions->count() > 0) {
            foreach ($permissions as $perm) {
                echo "   - Temporada {$perm->season_id}: {$perm->role}\n";
            }
        } else {
            echo "   - ❌ SIN PERMISOS\n";
        }
    } else {
        echo "   - ❌ USUARIO NO ENCONTRADO\n";
    }
    echo "\n";
}

// 3. Give permissions for ALL existing seasons
echo "🔧 ASIGNANDO PERMISOS PARA TODAS LAS TEMPORADAS...\n";

foreach ($users as $email) {
    $user = User::where('email', $email)->first();
    
    if ($user) {
        echo "Procesando {$email}...\n";
        
        foreach ($allSeasons as $season) {
            DB::table('user_season_roles')->updateOrInsert(
                ['user_id' => $user->id, 'season_id' => $season->id],
                [
                    'role' => 'admin',
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
            echo "   ✅ Temporada {$season->id} ({$season->name})\n";
        }
    }
}

echo "\n✅ PROCESO COMPLETADO!\n";
echo "Ahora los usuarios deberían tener acceso a TODAS las temporadas.\n";