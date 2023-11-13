<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
      User::create([
            'id' => 1,
            'username' => 'superadmin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'), // Asegúrate de hashear las contraseñas
            'image' => 'default.jpg',
            'type' => 'superadmin',
            'active' => 1,
            'recover_token' => null,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
            'logout' => 0,
        ]);

        $user = User::create([
            'id' => 2, // Asegúrate de que el ID sea único
            'username' => 'schooltesting',
            'email' => 'schooltesting@example.com',
            'password' => Hash::make('password'),
            'image' => 'default.jpg',
            'type' => 'admin',
            'active' => 1,
            'recover_token' => null,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
            'logout' => 0,
        ]);
        $user->setInitialPermissionsByRole();
    }
}
