<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class UserPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find users by email
        $adminUser = User::where('email', 'admin@escuela-test-v5.com')->first();
        $multiUser = User::where('email', 'multi@admin-test-v5.com')->first();

        // Check if users exist and create permissions
        if ($adminUser) {
            // Give admin permissions for all seasons (11, 12, etc.)
            $seasons = [11, 12, 13]; // Add more season IDs as needed
            
            foreach ($seasons as $seasonId) {
                DB::table('user_season_roles')->updateOrInsert(
                    ['user_id' => $adminUser->id, 'season_id' => $seasonId],
                    [
                        'role' => 'admin',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
            }
            
            $this->command->info("✅ Permisos dados a {$adminUser->email} para temporadas: " . implode(', ', $seasons));
        } else {
            $this->command->error("❌ Usuario admin@escuela-test-v5.com no encontrado");
        }

        if ($multiUser) {
            // Give admin permissions for all seasons
            $seasons = [11, 12, 13]; // Add more season IDs as needed
            
            foreach ($seasons as $seasonId) {
                DB::table('user_season_roles')->updateOrInsert(
                    ['user_id' => $multiUser->id, 'season_id' => $seasonId],
                    [
                        'role' => 'admin',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
            }
            
            $this->command->info("✅ Permisos dados a {$multiUser->email} para temporadas: " . implode(', ', $seasons));
        } else {
            $this->command->error("❌ Usuario multi@admin-test-v5.com no encontrado");
        }

        // Show final status
        $this->command->info("🔍 Verificando permisos creados:");
        $permissions = DB::table('user_season_roles')
            ->join('users', 'user_season_roles.user_id', '=', 'users.id')
            ->select('users.email', 'user_season_roles.season_id', 'user_season_roles.role')
            ->whereIn('users.email', ['admin@escuela-test-v5.com', 'multi@admin-test-v5.com'])
            ->get();

        foreach ($permissions as $permission) {
            $this->command->info("👤 {$permission->email} -> Temporada {$permission->season_id} -> Rol: {$permission->role}");
        }
    }
}