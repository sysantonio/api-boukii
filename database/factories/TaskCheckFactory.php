<?php

namespace Database\Factories;

use App\Models\TaskCheck;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Task;

class TaskCheckFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TaskCheck::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $task = Task::first();
        if (!$task) {
            $task = Task::factory()->create();
        }

        return [
            'text' => $this->faker->text($this->faker->numberBetween(5, 200)),
            'checked' => $this->faker->boolean,
            'task_id' => $this->faker->word,
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
