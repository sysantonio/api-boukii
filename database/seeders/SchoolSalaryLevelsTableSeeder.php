<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;
use App\Models\SchoolSalaryLevel;

class SchoolSalaryLevelsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $school = School::firstOrCreate(
            ['slug' => 'default-school'],
            [
                'name' => 'Default School',
                'description' => 'Initial school for salary levels',
                'settings' => '{}',
            ]
        );

        $levels = [
            ['name' => 'Junior', 'pay' => 20],
            ['name' => 'Senior', 'pay' => 30],
        ];

        foreach ($levels as $level) {
            SchoolSalaryLevel::firstOrCreate(
                ['school_id' => $school->id, 'name' => $level['name']],
                ['pay' => $level['pay'], 'active' => true]
            );
        }
    }
}
