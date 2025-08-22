<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sport;
use App\Models\SportType;

class SportsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = ['Hiver', 'Été', 'Autres'];

        $typeIds = [];
        foreach ($types as $typeName) {
            $type = SportType::firstOrCreate(['name' => $typeName]);
            $typeIds[$typeName] = $type->id;
        }

        $sports = [
            ['name' => 'Ski', 'sport_type' => $typeIds['Hiver']],
            ['name' => 'Snowboard', 'sport_type' => $typeIds['Hiver']],
            ['name' => 'VTT', 'sport_type' => $typeIds['Été']],
        ];

        foreach ($sports as $sport) {
            Sport::firstOrCreate(
                ['name' => $sport['name']],
                [
                    'icon_collective' => 'icon.png',
                    'icon_prive' => 'icon.png',
                    'icon_activity' => 'icon.png',
                    'icon_selected' => 'icon.png',
                    'icon_unselected' => 'icon.png',
                    'sport_type' => $sport['sport_type'],
                ]
            );
        }
    }
}
