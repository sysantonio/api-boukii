<?php

namespace Database\Factories;

use App\Models\MonitorSportAuthorizedDegree;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Degree;
use App\Models\MonitorSportsDegree;

class MonitorSportAuthorizedDegreeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MonitorSportAuthorizedDegree::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        
        $monitorSportsDegree = MonitorSportsDegree::first();
        if (!$monitorSportsDegree) {
            $monitorSportsDegree = MonitorSportsDegree::factory()->create();
        }

        return [
            'monitor_sport_id' => $this->faker->word,
            'degree_id' => $this->faker->word,
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
