<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class CiSmokeSeeder extends Seeder
{
    public function run(): void
    {
        if (Schema::hasTable('schools') && DB::table('schools')->count() === 0) {
            DB::table('schools')->insert([
                'name' => 'CI School',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (Schema::hasTable('users') && DB::table('users')->count() === 0) {
            DB::table('users')->insert([
                'name' => 'CI User',
                'email' => 'ci@example.com',
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
