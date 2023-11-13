<?php

namespace Database\Factories;

use App\Models\CourseExtra;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Course;

class CourseExtraFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CourseExtra::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $course = Course::first();
        if (!$course) {
            $course = Course::factory()->create();
        }

        return [
            'course_id' => $this->faker->word,
            'name' => $this->faker->text($this->faker->numberBetween(5, 255)),
            'description' => $this->faker->text($this->faker->numberBetween(5, 255)),
            'price' => $this->faker->numberBetween(0, 9223372036854775807),
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
