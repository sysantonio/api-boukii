<?php

require_once 'vendor/autoload.php';
require_once 'bootstrap/app.php';

use App\Models\User;
use App\Models\School;
use App\Models\SchoolUser;
use App\Models\Season;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

try {
    echo "=== Creando usuario admin para school_id=2 ===" . PHP_EOL;
    
    // 1. Crear o encontrar usuario
    $user = User::firstOrCreate(
        ['email' => 'admin@escuela-test-v5.com'],
        [
            'first_name' => 'Admin',
            'last_name' => 'Test V5',
            'password' => Hash::make('admin123'),
            'active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]
    );
    echo "✅ Usuario: {$user->email} (ID: {$user->id})" . PHP_EOL;
    
    // 2. Verificar/crear escuela
    $school = School::find(2);
    if (!$school) {
        $school = School::create([
            'name' => 'Escuela de Esquí Test V5',
            'active' => true,
            'slug' => 'escuela-test-v5',
            'address' => 'Calle Test 123, Madrid',
            'phone' => '+34 123 456 789',
            'email' => 'admin@escuela-test-v5.com',
            'owner_id' => $user->id
        ]);
        echo "✅ Escuela creada: {$school->name} (ID: {$school->id})" . PHP_EOL;
    } else {
        echo "✅ Escuela encontrada: {$school->name} (ID: {$school->id})" . PHP_EOL;
    }
    
    // 3. Asignar rol school_admin
    $role = Role::firstOrCreate(['name' => 'school_admin']);
    if (!$user->hasRole('school_admin')) {
        $user->assignRole('school_admin');
        echo "✅ Rol school_admin asignado" . PHP_EOL;
    } else {
        echo "✅ Usuario ya tiene rol school_admin" . PHP_EOL;
    }
    
    // 4. Crear relación user-school
    $schoolUser = SchoolUser::firstOrCreate([
        'user_id' => $user->id,
        'school_id' => $school->id
    ]);
    echo "✅ Relación user-school creada/verificada" . PHP_EOL;
    
    // 5. Crear temporadas de prueba si la tabla seasons existe
    if (Schema::hasTable('seasons')) {
        $currentSeason = Season::firstOrCreate(
            [
                'school_id' => $school->id,
                'name' => 'Temporada 2024-2025'
            ],
            [
                'start_date' => '2024-12-01',
                'end_date' => '2025-04-30',
                'is_active' => true,
                'is_current' => true,
                'hour_start' => '08:00:00',
                'hour_end' => '18:00:00'
            ]
        );
        echo "✅ Temporada actual: {$currentSeason->name} (ID: {$currentSeason->id})" . PHP_EOL;
        
        $futureSeason = Season::firstOrCreate(
            [
                'school_id' => $school->id,
                'name' => 'Temporada 2025-2026'
            ],
            [
                'start_date' => '2025-12-01',
                'end_date' => '2026-04-30',
                'is_active' => true,
                'is_current' => false,
                'hour_start' => '08:00:00',
                'hour_end' => '18:00:00'
            ]
        );
        echo "✅ Temporada futura: {$futureSeason->name} (ID: {$futureSeason->id})" . PHP_EOL;
    } else {
        echo "⚠️ Tabla seasons no existe aún" . PHP_EOL;
    }
    
    echo PHP_EOL . "=== DATOS DE PRUEBA CREADOS ===" . PHP_EOL;
    echo "Email: admin@escuela-test-v5.com" . PHP_EOL;
    echo "Password: admin123" . PHP_EOL;
    echo "School ID: {$school->id}" . PHP_EOL;
    echo "User ID: {$user->id}" . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "Trace: " . $e->getTraceAsString() . PHP_EOL;
}