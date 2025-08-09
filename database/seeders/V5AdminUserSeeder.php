<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\School;
use App\Models\Season;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;

class V5AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates admin user and test data for V5 development
     */
    public function run(): void
    {
        // Ensure roles exist
        if (!Role::where('name', 'school_admin')->exists()) {
            Role::create(['name' => 'school_admin', 'guard_name' => 'web']);
        }

        // Ensure school exists without modifying existing data
        $school = School::firstOrCreate(
            ['id' => 2],
            [
                'name' => 'Escuela de Esquí Test V5',
                'slug' => 'escuela-test-v5',
                'is_active' => true,
                'email' => 'admin@escuela-test-v5.com',
                'phone' => '+34 123 456 789',
                'address' => 'Pista Principal, Sierra Nevada, España',
                'description' => 'Escuela de prueba para desarrollo V5 del sistema Boukii',
                'website' => 'https://escuela-test-v5.com',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // Create admin user
        $adminUser = User::updateOrCreate(
            ['email' => 'admin@escuela-test-v5.com'],
            [
                'username' => 'admin-test-v5',
                'first_name' => 'Admin Test',
                'last_name' => 'V5',
                'password' => Hash::make('admin123'),
                'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&h=150&fit=crop&crop=face',
                'type' => 'admin',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // Assign role to user
        if (!$adminUser->hasRole('school_admin')) {
            $adminUser->assignRole('school_admin');
        }

        // Associate user with school via SchoolUser pivot table
        if (!\App\Models\SchoolUser::where('user_id', $adminUser->id)->where('school_id', $school->id)->exists()) {
            \App\Models\SchoolUser::create([
                'user_id' => $adminUser->id,
                'school_id' => $school->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Create current season (active and current)
        $currentSeason = Season::updateOrCreate(
            [
                'school_id' => $school->id,
                'name' => 'Temporada 2024-2025'
            ],
            [
                'start_date' => '2024-12-01',
                'end_date' => '2025-04-30',
                'hour_start' => '08:00:00',
                'hour_end' => '18:00:00',
                'is_active' => true,
                'is_current' => true,
                'is_historical' => false,
                'vacation_days' => json_encode([
                    '2024-12-25', '2024-12-26', // Christmas
                    '2025-01-01', '2025-01-06', // New Year
                    '2025-04-18', '2025-04-21'  // Easter
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // Create future season
        $futureSeason = Season::updateOrCreate(
            [
                'school_id' => $school->id,
                'name' => 'Temporada 2025-2026'
            ],
            [
                'start_date' => '2025-12-01',
                'end_date' => '2026-04-30',
                'hour_start' => '08:00:00',
                'hour_end' => '18:00:00',
                'is_active' => true,
                'is_current' => false,
                'is_historical' => false,
                'vacation_days' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // Create historical season
        $historicalSeason = Season::updateOrCreate(
            [
                'school_id' => $school->id,
                'name' => 'Temporada 2023-2024'
            ],
            [
                'start_date' => '2023-12-01',
                'end_date' => '2024-04-30',
                'hour_start' => '08:00:00',
                'hour_end' => '18:00:00',
                'is_active' => false,
                'is_current' => false,
                'is_historical' => true,
                'vacation_days' => json_encode([]),
                'created_at' => Carbon::parse('2023-12-01'),
                'updated_at' => Carbon::parse('2024-04-30')
            ]
        );

        // Try to assign user to current season (handle missing columns gracefully)
        try {
            if (\Schema::hasTable('user_season_roles')) {
                \DB::table('user_season_roles')->updateOrInsert(
                    [
                        'user_id' => $adminUser->id,
                        'season_id' => $currentSeason->id
                    ],
                    [
                        'role' => 'school_admin',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
            } else {
                $this->command->warn('user_season_roles table not found - skipping season role assignment');
            }
        } catch (\Exception $e) {
            $this->command->warn('Could not assign season role: ' . $e->getMessage());
        }

        $this->command->info('V5 Admin user created successfully!');
        $this->command->info('Email: admin@escuela-test-v5.com');
        $this->command->info('Password: admin123');
        $this->command->info('School: Escuela de Esquí Test V5 (ID: 2)');
        $this->command->info('Current Season: Temporada 2024-2025');
        $this->command->line('');
        $this->command->info('Additional seasons created:');
        $this->command->info('- Temporada 2025-2026 (Future)');
        $this->command->info('- Temporada 2023-2024 (Historical)');
    }
}