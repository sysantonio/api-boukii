<?php

namespace Database\Factories;

use App\Models\MonitorSportsDegree;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Degree;
use App\Models\Sport;
use App\Models\Monitor;
use App\Models\School;

class MonitorSportsDegreeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MonitorSportsDegree::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        
        $school = School::first();
        if (!$school) {
            $school = School::factory()->create();
        }

        return [
            'sport_id' => $this->faker->word,
            'school_id' => $this->faker->word,
            'degree_id' => $this->faker->word,
            'monitor_id' => $this->faker->word,
            'salary_level' => $this->faker->word,
            'allow_adults' => $this->faker->word,
            'is_default' => $this->faker->boolean,
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
