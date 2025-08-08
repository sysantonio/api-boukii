<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Season;
use App\Models\School;
use App\Models\User;
use Carbon\Carbon;

class V5TestSeasonsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schoolId = 2; // Escuela de prueba según especificaciones
        
        // Verificar que la escuela existe
        $school = School::find($schoolId);
        if (!$school) {
            $this->command->info("School with ID {$schoolId} not found. Creating test school...");
            
            $school = School::create([
                'name' => 'Escuela de Esquí Test V5',
                'slug' => 'escuela-test-v5',
                'is_active' => true,
                'timezone' => 'Europe/Madrid',
                'currency' => 'EUR',
                'owner_id' => 1, // Asumiendo que existe un usuario con ID 1
            ]);
            
            $schoolId = $school->id;
        }

        // Crear temporadas de prueba si no existen
        $currentYear = Carbon::now()->year;
        $seasons = [
            [
                'name' => "Temporada {$currentYear}-" . ($currentYear + 1),
                'start_date' => Carbon::create($currentYear, 12, 1),
                'end_date' => Carbon::create($currentYear + 1, 4, 30),
                'is_active' => true,
                'is_current' => true,
                'is_historical' => false,
            ],
            [
                'name' => "Temporada " . ($currentYear - 1) . "-{$currentYear}",
                'start_date' => Carbon::create($currentYear - 1, 12, 1),
                'end_date' => Carbon::create($currentYear, 4, 30),
                'is_active' => false,
                'is_current' => false,
                'is_historical' => true,
            ],
            [
                'name' => "Temporada " . ($currentYear + 1) . "-" . ($currentYear + 2),
                'start_date' => Carbon::create($currentYear + 1, 12, 1),
                'end_date' => Carbon::create($currentYear + 2, 4, 30),
                'is_active' => true,
                'is_current' => false,
                'is_historical' => false,
            ],
        ];

        foreach ($seasons as $seasonData) {
            $existingSeason = Season::where('school_id', $schoolId)
                ->where('name', $seasonData['name'])
                ->first();

            if (!$existingSeason) {
                Season::create(array_merge($seasonData, [
                    'school_id' => $schoolId,
                    'hour_start' => '08:00:00',
                    'hour_end' => '18:00:00',
                    'vacation_days' => json_encode(['2024-12-25', '2025-01-01']),
                ]));

                $this->command->info("Created season: {$seasonData['name']} for school ID {$schoolId}");
            }
        }

        // Crear usuario de prueba school_admin si no existe
        $testUser = User::where('email', 'admin@escuela-test.com')->first();
        if (!$testUser) {
            $testUser = User::create([
                'name' => 'Admin Test V5',
                'email' => 'admin@escuela-test.com',
                'password' => bcrypt('password123'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            // Asignar rol de school_admin (usando Spatie Permission)
            $testUser->assignRole('school_admin');

            // Asociar usuario con la escuela
            $testUser->schools()->attach($schoolId);

            $this->command->info("Created test user: admin@escuela-test.com with password: password123");
        }

        $this->command->info("V5 Test seasons and user setup completed for school ID {$schoolId}");
    }
}