<?php

namespace Database\Factories;

use App\Models\StationsSchool;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\School;
use App\Models\Station;

class StationsSchoolFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StationsSchool::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        
        $station = Station::first();
        if (!$station) {
            $station = Station::factory()->create();
        }

        return [
            'station_id' => $this->faker->word,
            'school_id' => $this->faker->word,
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
