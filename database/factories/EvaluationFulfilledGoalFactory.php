<?php

namespace Database\Factories;

use App\Models\EvaluationFulfilledGoal;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Evaluation;

class EvaluationFulfilledGoalFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EvaluationFulfilledGoal::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $evaluation = Evaluation::first();
        if (!$evaluation) {
            $evaluation = Evaluation::factory()->create();
        }

        return [
            'evaluation_id' => $this->faker->word,
            'degrees_school_sport_goals_id' => $this->faker->word,
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
