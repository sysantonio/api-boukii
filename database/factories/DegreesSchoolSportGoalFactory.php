<?php

namespace Database\Factories;

use App\Models\DegreesSchoolSportGoal;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Degree;

class DegreesSchoolSportGoalFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DegreesSchoolSportGoal::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $degree = Degree::first();
        if (!$degree) {
            $degree = Degree::factory()->create();
        }

        return [
            'degree_id' => $this->faker->word,
            'name' => $this->faker->text($this->faker->numberBetween(5, 65535)),
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
