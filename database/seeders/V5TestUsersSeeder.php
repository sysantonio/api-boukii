<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Season;
use App\Models\School;

class V5TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create multi-school admin user
        $multiSchoolAdmin = User::updateOrCreate(
            ['email' => 'admin@boukii-v5.com'],
            [
                'username' => 'admin_multi',
                'first_name' => 'Admin',
                'last_name' => 'Boukii',
                'password' => Hash::make('password'),
                'type' => 'admin',
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Create school 2 admin user
        $schoolTwoAdmin = User::updateOrCreate(
            ['email' => 'multi@boukii-v5.com'],
            [
                'username' => 'school2_admin',
                'first_name' => 'Multi',
                'last_name' => 'School2',
                'password' => Hash::make('password'),
                'type' => 'admin',
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->command->info("Users created: admin@boukii-v5.com (ID: {$multiSchoolAdmin->id}), multi@boukii-v5.com (ID: {$schoolTwoAdmin->id})");

        // Associate multi-school admin with all schools
        $schoolIds = School::pluck('id');
        foreach ($schoolIds as $schoolId) {
            DB::table('school_users')->updateOrInsert(
                ['school_id' => $schoolId, 'user_id' => $multiSchoolAdmin->id],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        // Associate second admin only with school 2
        DB::table('school_users')->updateOrInsert(
            ['school_id' => 2, 'user_id' => $schoolTwoAdmin->id],
            ['created_at' => now(), 'updated_at' => now()]
        );

        // Use existing season ID 6 from school 2 if exists, otherwise create new season
        $existingSeason = Season::where('school_id', 2)->first();
        if ($existingSeason) {
            $season = $existingSeason;
            $this->command->info("Using existing season: {$season->name} (ID: {$season->id})");
        } else {
            // Only create if no season exists for school 2
            $season = Season::create([
                'name' => 'Temporada 2025-2026',
                'school_id' => 2,
                'start_date' => '2025-12-01',
                'end_date' => '2026-04-30',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->command->info("Created new season: {$season->name} (ID: {$season->id})");
        }

        // Assign admin permissions to both users for the existing season
        DB::table('user_season_roles')->updateOrInsert(
            ['user_id' => $multiSchoolAdmin->id, 'season_id' => $season->id],
            ['role' => 'admin', 'created_at' => now(), 'updated_at' => now()]
        );

        DB::table('user_season_roles')->updateOrInsert(
            ['user_id' => $schoolTwoAdmin->id, 'season_id' => $season->id],
            ['role' => 'admin', 'created_at' => now(), 'updated_at' => now()]
        );

        $this->command->info("Admin permissions assigned for season: {$season->name} (ID: {$season->id})");

        $this->command->info('V5 Test Users and permissions setup completed!');
    }
}