<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Season;
use App\Models\School;
use Carbon\Carbon;

class V5TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin test v5 user
        $adminUser = User::updateOrCreate(
            ['email' => 'admin.test.v5@boukii.com'],
            [
                'username' => 'admin_test_v5',
                'first_name' => 'Admin',
                'last_name' => 'Test V5',
                'password' => Hash::make('password'),
                'type' => 'admin',
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Create multi test user
        $multiUser = User::updateOrCreate(
            ['email' => 'multi.test@boukii.com'],
            [
                'username' => 'multi_test',
                'first_name' => 'Multi',
                'last_name' => 'Test',
                'password' => Hash::make('password'),
                'type' => 'admin',
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->command->info("Users created: Admin Test V5 (ID: {$adminUser->id}), Multi Test (ID: {$multiUser->id})");

        // Find school 2 (ESS Veveyse)
        $school = School::find(2);
        if (!$school) {
            $this->command->error('School 2 not found!');
            return;
        }

        // Associate users with school 2
        DB::table('school_users')->updateOrInsert(
            ['school_id' => 2, 'user_id' => $adminUser->id],
            ['created_at' => now(), 'updated_at' => now()]
        );

        DB::table('school_users')->updateOrInsert(
            ['school_id' => 2, 'user_id' => $multiUser->id],
            ['created_at' => now(), 'updated_at' => now()]
        );

        $this->command->info("Users associated with school: {$school->name}");

        // Create or find 2025-2026 season
        $season = Season::updateOrCreate(
            ['name' => 'Temporada 2025-2026', 'school_id' => 2],
            [
                'start_date' => '2025-12-01',
                'end_date' => '2026-04-30',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->command->info("Season created/updated: {$season->name} (ID: {$season->id})");

        // Create user_season_roles table if it doesn't exist
        if (!DB::getSchemaBuilder()->hasTable('user_season_roles')) {
            DB::statement('
                CREATE TABLE user_season_roles (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    user_id BIGINT UNSIGNED NOT NULL,
                    season_id BIGINT UNSIGNED NOT NULL,
                    role VARCHAR(255) NOT NULL,
                    created_at TIMESTAMP NULL DEFAULT NULL,
                    updated_at TIMESTAMP NULL DEFAULT NULL,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (season_id) REFERENCES seasons(id) ON DELETE CASCADE,
                    UNIQUE KEY unique_user_season (user_id, season_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ');
            $this->command->info('user_season_roles table created');
        }

        // Assign admin permissions to both users for the season
        DB::table('user_season_roles')->updateOrInsert(
            ['user_id' => $adminUser->id, 'season_id' => $season->id],
            ['role' => 'admin', 'created_at' => now(), 'updated_at' => now()]
        );

        DB::table('user_season_roles')->updateOrInsert(
            ['user_id' => $multiUser->id, 'season_id' => $season->id],
            ['role' => 'admin', 'created_at' => now(), 'updated_at' => now()]
        );

        $this->command->info("Admin permissions assigned for season: {$season->name}");

        // Also check for season 11 and assign permissions if it exists
        $season11 = Season::find(11);
        if ($season11) {
            DB::table('user_season_roles')->updateOrInsert(
                ['user_id' => $adminUser->id, 'season_id' => 11],
                ['role' => 'admin', 'created_at' => now(), 'updated_at' => now()]
            );

            DB::table('user_season_roles')->updateOrInsert(
                ['user_id' => $multiUser->id, 'season_id' => 11],
                ['role' => 'admin', 'created_at' => now(), 'updated_at' => now()]
            );

            $this->command->info("Admin permissions also assigned for season 11: {$season11->name}");
        }

        $this->command->info('V5 Test Users and permissions setup completed!');
    }
}