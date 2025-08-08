<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use App\V5\Models\Season;
use App\Models\School;
use Carbon\Carbon;

class SeasonSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        // Check if seasons table exists
        if (!Schema::hasTable('seasons')) {
            $this->command->info('Seasons table does not exist, skipping SeasonSeeder');
            return;
        }

        // Get the first school
        $school = School::first();
        
        if (!$school) {
            $this->command->warn('No schools found, creating default school');
            $school = School::create([
                'name' => 'Escuela de Esquí Demo',
                'address' => 'Sierra Nevada, Granada',
                'phone' => '+34 958 480 000',
                'email' => 'info@demo-ski.com',
                'active' => 1
            ]);
        }

        // Create current season if it doesn't exist
        $currentSeason = Season::where('is_active', true)
            ->where('school_id', $school->id)
            ->first();

        if (!$currentSeason) {
            $this->command->info('Creating default active season');
            
            Season::create([
                'name' => 'Temporada 2024-2025',
                'start_date' => Carbon::create(2024, 12, 1),
                'end_date' => Carbon::create(2025, 4, 30),
                'hour_start' => '09:00:00',
                'hour_end' => '17:00:00',
                'is_active' => true,
                'school_id' => $school->id,
                'vacation_days' => null
            ]);

            $this->command->info('Default season created successfully');
        } else {
            $this->command->info('Active season already exists: ' . $currentSeason->name);
        }

        // Create some additional seasons for testing  
        $seasons = [
            [
                'name' => 'Temporada 2023-2024',
                'start_date' => Carbon::create(2023, 12, 1),
                'end_date' => Carbon::create(2024, 4, 30),
                'hour_start' => '09:00:00',
                'hour_end' => '17:00:00',
                'is_active' => false,
                'school_id' => $school->id,
                'vacation_days' => null
            ],
            [
                'name' => 'Temporada 2025-2026',
                'start_date' => Carbon::create(2025, 12, 1),
                'end_date' => Carbon::create(2026, 4, 30),
                'hour_start' => '09:00:00',
                'hour_end' => '17:00:00',
                'is_active' => false,
                'school_id' => $school->id,
                'vacation_days' => null
            ]
        ];

        foreach ($seasons as $seasonData) {
            $exists = Season::where('name', $seasonData['name'])
                ->where('school_id', $school->id)
                ->exists();

            if (!$exists) {
                Season::create($seasonData);
                $this->command->info('Created season: ' . $seasonData['name']);
            }
        }
    }
}