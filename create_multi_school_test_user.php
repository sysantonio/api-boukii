<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\School;
use App\Models\SchoolUser;
use App\Models\Season;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

try {
    echo "=== Creando usuario con múltiples escuelas ===" . PHP_EOL;
    
    // 1. Crear usuario de prueba
    $multiUser = User::firstOrCreate(
        ['email' => 'multi@admin-test-v5.com'],
        [
            'first_name' => 'Multi School',
            'last_name' => 'Admin',
            'password' => Hash::make('multi123'),
            'active' => true,
            'type' => 'admin', // Agregar campo type requerido
            'created_at' => now(),
            'updated_at' => now()
        ]
    );
    echo "✅ Usuario multi-escuela: {$multiUser->email} (ID: {$multiUser->id})" . PHP_EOL;
    
    // 2. Asegurar que tenemos las escuelas necesarias
    $school1 = School::firstOrCreate(
        ['id' => 1],
        [
            'name' => 'Escuela Principal',
            'active' => true,
            'slug' => 'escuela-principal',
            'address' => 'Calle Principal 1',
            'phone' => '+34 111 111 111',
            'email' => 'admin@escuela-principal.com'
        ]
    );
    
    $school2 = School::find(2); // Ya existe de antes
    
    $school3 = School::firstOrCreate(
        ['id' => 3],
        [
            'name' => 'Escuela Secundaria',
            'active' => true,
            'slug' => 'escuela-secundaria',
            'address' => 'Calle Secundaria 3',
            'phone' => '+34 333 333 333',
            'email' => 'admin@escuela-secundaria.com'
        ]
    );
    
    echo "✅ Escuelas disponibles:" . PHP_EOL;
    echo "  - {$school1->name} (ID: {$school1->id})" . PHP_EOL;
    echo "  - {$school2->name} (ID: {$school2->id})" . PHP_EOL;
    echo "  - {$school3->name} (ID: {$school3->id})" . PHP_EOL;
    
    // 3. Asignar rol school_admin
    $role = Role::firstOrCreate(['name' => 'school_admin']);
    if (!$multiUser->hasRole('school_admin')) {
        $multiUser->assignRole('school_admin');
        echo "✅ Rol school_admin asignado" . PHP_EOL;
    }
    
    // 4. Crear relaciones user-school
    $relations = [
        ['user_id' => $multiUser->id, 'school_id' => 1],
        ['user_id' => $multiUser->id, 'school_id' => 2],
        ['user_id' => $multiUser->id, 'school_id' => 3],
    ];
    
    foreach ($relations as $relation) {
        SchoolUser::firstOrCreate($relation);
    }
    echo "✅ Relaciones user-school creadas para 3 escuelas" . PHP_EOL;
    
    // 5. Crear temporadas para las escuelas (si no existen)
    $seasons = [
        ['school_id' => 1, 'name' => 'Temporada Principal 2025', 'start_date' => '2025-01-01', 'end_date' => '2025-12-31'],
        ['school_id' => 2, 'name' => 'Temporada Test 2025', 'start_date' => '2025-01-01', 'end_date' => '2025-12-31'], // Ya existe
        ['school_id' => 3, 'name' => 'Temporada Secundaria 2025', 'start_date' => '2025-01-01', 'end_date' => '2025-12-31'],
    ];
    
    foreach ($seasons as $seasonData) {
        $season = Season::firstOrCreate(
            ['school_id' => $seasonData['school_id'], 'name' => $seasonData['name']],
            [
                'start_date' => $seasonData['start_date'],
                'end_date' => $seasonData['end_date'],
                'is_active' => true,
                'is_current' => true,
                'hour_start' => '08:00:00',
                'hour_end' => '18:00:00'
            ]
        );
        echo "✅ Temporada: {$season->name} para escuela {$seasonData['school_id']}" . PHP_EOL;
    }
    
    echo PHP_EOL . "=== DATOS DE PRUEBA CREADOS ===" . PHP_EOL;
    echo "Usuario con 1 escuela:" . PHP_EOL;
    echo "  Email: admin@escuela-test-v5.com" . PHP_EOL;
    echo "  Password: admin123" . PHP_EOL;
    echo "  Escuelas: 1 (ESS Veveyse)" . PHP_EOL;
    echo PHP_EOL;
    echo "Usuario con múltiples escuelas:" . PHP_EOL;
    echo "  Email: multi@admin-test-v5.com" . PHP_EOL;
    echo "  Password: multi123" . PHP_EOL;
    echo "  Escuelas: 3 (Principal, ESS Veveyse, Secundaria)" . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "Trace: " . $e->getTraceAsString() . PHP_EOL;
}