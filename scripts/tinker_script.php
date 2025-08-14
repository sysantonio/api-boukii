// Create admin user for school_id=2
$user = App\Models\User::firstOrCreate(
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
echo "Usuario: {$user->email} (ID: {$user->id})" . PHP_EOL;

// Check if school_id=2 exists
$school = App\Models\School::find(2);
if (!$school) {
    $school = App\Models\School::create([
        'name' => 'Escuela de Esquí Test V5',
        'active' => true,
        'slug' => 'escuela-test-v5',
        'address' => 'Calle Test 123',
        'phone' => '+34 123 456 789',
        'email' => 'admin@escuela-test-v5.com',
        'owner_id' => $user->id
    ]);
    echo "Escuela creada: {$school->name}" . PHP_EOL;
} else {
    echo "Escuela: {$school->name}" . PHP_EOL;
}

// Assign school_admin role
try {
    if (!$user->hasRole('school_admin')) {
        $user->assignRole('school_admin');
        echo "Rol school_admin asignado" . PHP_EOL;
    } else {
        echo "Usuario ya tiene rol school_admin" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "Error con rol: " . $e->getMessage() . PHP_EOL;
}

// Create user-school relationship
try {
    App\Models\SchoolUser::firstOrCreate([
        'user_id' => $user->id,
        'school_id' => $school->id
    ]);
    echo "Relación user-school OK" . PHP_EOL;
} catch (Exception $e) {
    echo "Error user-school: " . $e->getMessage() . PHP_EOL;
}

echo "=== LISTO PARA PROBAR ===" . PHP_EOL;
echo "Email: admin@escuela-test-v5.com" . PHP_EOL;
echo "Password: admin123" . PHP_EOL;