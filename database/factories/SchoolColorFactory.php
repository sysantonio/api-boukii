<?php

namespace Database\Factories;

use App\Models\SchoolColor;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\School;

class SchoolColorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SchoolColor::class;

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
            'color' => $this->faker->text($this->faker->numberBetween(5, 45)),
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
