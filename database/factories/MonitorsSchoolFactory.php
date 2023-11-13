<?php

namespace Database\Factories;

use App\Models\MonitorsSchool;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\School;
use App\Models\Station;
use App\Models\Monitor;

class MonitorsSchoolFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MonitorsSchool::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        
        $monitor = Monitor::first();
        if (!$monitor) {
            $monitor = Monitor::factory()->create();
        }

        return [
            'monitor_id' => $this->faker->word,
            'school_id' => $this->faker->word,
            'station_id' => $this->faker->word,
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => $this->faker->date('Y-m-d H:i:s'),
            'status_updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'accepted_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
