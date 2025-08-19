<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CREATING V5 TEST USERS ===\n";

// Verificar escuelas disponibles
echo "Available schools:\n";
$schools = App\Models\School::all(['id', 'name']);
foreach ($schools as $school) {
    echo "  - ID: {$school->id} - {$school->name}\n";
}

if ($schools->count() < 2) {
    echo "Error: Need at least 2 schools. Found: {$schools->count()}\n";
    exit(1);
}

$school2 = $schools->firstWhere('id', 2) ?: $schools->skip(1)->first();
$school3 = $schools->firstWhere('id', 3) ?: $schools->first();

echo "\nUsing schools:\n";
echo "School for User 1 (single): ID {$school2->id} - {$school2->name}\n";
echo "Schools for User 2 (multi): ID {$school2->id} - {$school2->name} & ID {$school3->id} - {$school3->name}\n";

// Usuario 1: Solo relacionado a una escuela
echo "\n--- Creating User 1 (Single school) ---\n";
$user1 = App\Models\User::where('email', 'user1@boukii.test')->first();
if (!$user1) {
    $user1 = App\Models\User::create([
        'username' => 'user1_boukii',
        'first_name' => 'Usuario',
        'last_name' => 'Uno',
        'email' => 'user1@boukii.test',
        'password' => password_hash('password123', PASSWORD_DEFAULT),
        'type' => 1, // Admin type
        'active' => 1,
    ]);
    echo "✓ Created user: {$user1->first_name} {$user1->last_name} ({$user1->email})\n";
} else {
    echo "✓ User already exists: {$user1->first_name} {$user1->last_name} ({$user1->email})\n";
}

// Usuario 2: Relacionado a múltiples escuelas
echo "\n--- Creating User 2 (Multiple schools) ---\n";
$user2 = App\Models\User::where('email', 'user2@boukii.test')->first();
if (!$user2) {
    $user2 = App\Models\User::create([
        'username' => 'user2_boukii',
        'first_name' => 'Usuario',
        'last_name' => 'Dos',  
        'email' => 'user2@boukii.test',
        'password' => password_hash('password123', PASSWORD_DEFAULT),
        'type' => 1, // Admin type
        'active' => 1,
    ]);
    echo "✓ Created user: {$user2->first_name} {$user2->last_name} ({$user2->email})\n";
} else {
    echo "✓ User already exists: {$user2->first_name} {$user2->last_name} ({$user2->email})\n";
}

// Crear/verificar temporadas para las escuelas
echo "\n--- Creating default seasons ---\n";
$season1 = App\Models\Season::firstOrCreate([
    'school_id' => $school2->id,
    'name' => 'Test Season ' . date('Y')
], [
    'start_date' => date('Y-01-01'),
    'end_date' => date('Y-12-31'),
    'is_active' => true
]);
echo "✓ Season for school {$school2->id}: {$season1->name}\n";

$season2 = App\Models\Season::firstOrCreate([
    'school_id' => $school3->id,
    'name' => 'Test Season ' . date('Y')
], [
    'start_date' => date('Y-01-01'), 
    'end_date' => date('Y-12-31'),
    'is_active' => true
]);
echo "✓ Season for school {$school3->id}: {$season2->name}\n";

// Crear relaciones user-season con roles
echo "\n--- Creating user-season-role relationships ---\n";

// User 1 -> Solo escuela 2
if (Schema::hasTable('user_season_roles')) {
    $role1 = DB::table('user_season_roles')->updateOrInsert([
        'user_id' => $user1->id,
        'season_id' => $season1->id
    ], [
        'role' => 'season.admin',
        'is_active' => true,
        'assigned_at' => now(),
        'created_at' => now(),
        'updated_at' => now()
    ]);
    echo "✓ User 1 assigned to season {$season1->id} ({$school2->name})\n";
    
    // User 2 -> Escuelas 2 y 3
    $role2a = DB::table('user_season_roles')->updateOrInsert([
        'user_id' => $user2->id,
        'season_id' => $season1->id
    ], [
        'role' => 'season.admin', 
        'is_active' => true,
        'assigned_at' => now(),
        'created_at' => now(),
        'updated_at' => now()
    ]);
    echo "✓ User 2 assigned to season {$season1->id} ({$school2->name})\n";
    
    $role2b = DB::table('user_season_roles')->updateOrInsert([
        'user_id' => $user2->id,
        'season_id' => $season2->id
    ], [
        'role' => 'season.admin',
        'is_active' => true, 
        'assigned_at' => now(),
        'created_at' => now(),
        'updated_at' => now()
    ]);
    echo "✓ User 2 assigned to season {$season2->id} ({$school3->name})\n";
} else {
    echo "⚠ user_season_roles table doesn't exist yet. Run V5 migrations first.\n";
}

echo "\n=== USERS CREATED SUCCESSFULLY ===\n";
echo "✓ User 1 (single school): user1@boukii.test / password123\n";
echo "  - School: {$school2->name} (ID: {$school2->id})\n";
echo "  - Season: {$season1->name} (ID: {$season1->id})\n";
echo "\n✓ User 2 (multiple schools): user2@boukii.test / password123\n";  
echo "  - Schools: {$school2->name} (ID: {$school2->id}) & {$school3->name} (ID: {$school3->id})\n";
echo "  - Seasons: {$season1->name} (ID: {$season1->id}) & {$season2->name} (ID: {$season2->id})\n";

echo "\n🎯 Ready for V5 authentication testing!\n";