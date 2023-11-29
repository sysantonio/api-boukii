<?php

namespace Database\Factories;

use App\Models\EvaluationFile;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Evaluation;

class EvaluationFileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EvaluationFile::class;

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
            'name' => $this->faker->text($this->faker->numberBetween(5, 255)),
            'type' => $this->faker->text($this->faker->numberBetween(5, 255)),
            'file' => $this->faker->text($this->faker->numberBetween(5, 4096)),
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
