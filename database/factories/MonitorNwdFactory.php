<?php

namespace Database\Factories;

use App\Models\MonitorNwd;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Monitor;
use App\Models\School;
use App\Models\Station;

class MonitorNwdFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MonitorNwd::class;

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
            'monitor_id' => $this->faker->word,
            'school_id' => $this->faker->word,
            'station_id' => $this->faker->word,
            'start_date' => $this->faker->date('Y-m-d'),
            'end_date' => $this->faker->date('Y-m-d'),
            'start_time' => $this->faker->date('H:i:s'),
            'end_time' => $this->faker->date('H:i:s'),
            'full_day' => $this->faker->boolean,
            'description' => $this->faker->text($this->faker->numberBetween(5, 65535)),
            'color' => $this->faker->text($this->faker->numberBetween(5, 45)),
            'user_nwd_subtype_id' => $this->faker->boolean,
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
