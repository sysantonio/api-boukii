<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CiSmokeSeeder extends Seeder
{
    public function run(): void
    {
        // Usuarios
        $userId = DB::table('users')->insertGetId([
            'name' => 'CI Admin',
            'email' => 'ci-admin@example.com',
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Escuela
        $schoolId = DB::table('schools')->insertGetId([
            'name' => 'CI School',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Temporada
        $seasonId = DB::table('seasons')->insertGetId([
            'school_id' => $schoolId,
            'name' => 'CI Season',
            'start_date' => now()->startOfYear(),
            'end_date' => now()->endOfYear(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Pivot
        DB::table('school_users')->insert([
            'school_id' => $schoolId,
            'user_id'   => $userId,
            'created_at'=> now(),
            'updated_at'=> now(),
        ]);
    }
}
