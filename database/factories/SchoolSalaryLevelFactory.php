<?php

namespace Database\Factories;

use App\Models\SchoolSalaryLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\School;

class SchoolSalaryLevelFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SchoolSalaryLevel::class;

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
            'school_id' => $this->faker->word,
            'name' => $this->faker->text($this->faker->numberBetween(5, 100)),
            'pay' => $this->faker->numberBetween(0, 9223372036854775807),
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
