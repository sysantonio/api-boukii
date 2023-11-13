<?php

namespace Database\Factories;

use App\Models\MonitorObservation;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Monitor;
use App\Models\School;

class MonitorObservationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MonitorObservation::class;

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
            'general' => $this->faker->text($this->faker->numberBetween(5, 5000)),
            'notes' => $this->faker->text($this->faker->numberBetween(5, 5000)),
            'historical' => $this->faker->text($this->faker->numberBetween(5, 5000)),
            'monitor_id' => $this->faker->word,
            'school_id' => $this->faker->word,
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
