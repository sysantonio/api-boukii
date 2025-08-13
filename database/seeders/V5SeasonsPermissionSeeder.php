<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\School;

/**
 * Seeder to give users seasons management permissions
 * This seeder ensures users can access the seasons management module
 */
class V5SeasonsPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 V5SeasonsPermissionSeeder: Starting seasons permission setup...');

        // Find users that should have seasons access
        $usersToUpdate = [
            'admin@boukii-v5.com',
            'multi@boukii-v5.com',
            'admin@escuela-test-v5.com',
            'multi@admin-test-v5.com',
            // Add your current test user email here if different
        ];

        // Find school ID 2 (the main test school)
        $school = School::find(2);
        if (!$school) {
            $this->command->error('❌ School ID 2 not found. Please check your schools table.');
            return;
        }

        $this->command->info("🏫 Working with school: {$school->name} (ID: {$school->id})");

        // First, check what columns exist in school_users table
        $columns = collect(DB::select('SHOW COLUMNS FROM school_users'))->pluck('Field');
        $this->command->info("📋 Table school_users has columns: " . $columns->implode(', '));

        foreach ($usersToUpdate as $email) {
            $user = User::where('email', $email)->first();
            
            if (!$user) {
                $this->command->warn("⚠️ User {$email} not found, skipping...");
                continue;
            }

            // Check if user is already associated with this school
            $existingSchoolUser = DB::table('school_users')
                ->where('user_id', $user->id)
                ->where('school_id', $school->id)
                ->first();

            if (!$existingSchoolUser) {
                // Insert new school_users record (without role column if it doesn't exist)
                $insertData = [
                    'user_id' => $user->id,
                    'school_id' => $school->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                
                // Only add role if column exists
                if ($columns->contains('role')) {
                    $insertData['role'] = 'admin';
                }
                
                DB::table('school_users')->insert($insertData);
                $this->command->info("✅ Added {$email} to school {$school->name}");
            } else {
                $this->command->info("ℹ️ {$email} already associated with school {$school->name}");
                
                // Update role if column exists
                if ($columns->contains('role')) {
                    DB::table('school_users')
                        ->where('user_id', $user->id)
                        ->where('school_id', $school->id)
                        ->update([
                            'role' => 'admin',
                            'updated_at' => now()
                        ]);
                    $this->command->info("✅ Updated {$email} role to admin");
                }
            }

            // Ensure user has superadmin role via Spatie Permission
            if (!$user->hasRole('superadmin')) {
                try {
                    $user->assignRole('superadmin');
                    $this->command->info("✅ Assigned superadmin role to {$email}");
                } catch (\Exception $e) {
                    $this->command->warn("⚠️ Could not assign superadmin role to {$email}: {$e->getMessage()}");
                    // If superadmin role doesn't exist, try to create it
                    try {
                        \Spatie\Permission\Models\Role::create(['name' => 'superadmin']);
                        $user->assignRole('superadmin');
                        $this->command->info("✅ Created and assigned superadmin role to {$email}");
                    } catch (\Exception $e2) {
                        $this->command->error("❌ Failed to create superadmin role: {$e2->getMessage()}");
                    }
                }
            } else {
                $this->command->info("ℹ️ {$email} already has superadmin role");
            }
        }

        // Show final permissions status
        $this->command->info('🔍 Final permissions status:');
        
        if ($columns->contains('role')) {
            $schoolUsers = DB::table('school_users')
                ->join('users', 'school_users.user_id', '=', 'users.id')
                ->where('school_users.school_id', $school->id)
                ->select('users.email', 'school_users.role')
                ->get();

            foreach ($schoolUsers as $schoolUser) {
                $this->command->info("👤 {$schoolUser->email} -> School Role: {$schoolUser->role}");
            }
        } else {
            $schoolUsers = DB::table('school_users')
                ->join('users', 'school_users.user_id', '=', 'users.id')
                ->where('school_users.school_id', $school->id)
                ->select('users.email')
                ->get();

            foreach ($schoolUsers as $schoolUser) {
                $this->command->info("👤 {$schoolUser->email} -> Associated with school");
            }
        }

        $this->command->info('✅ V5SeasonsPermissionSeeder completed successfully!');
        $this->command->info('🎯 Users should now be able to access /v5/seasons');
    }
}