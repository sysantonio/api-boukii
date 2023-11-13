<?php

namespace Database\Factories;

use App\Models\CourseDate;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Course;

class CourseDateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CourseDate::class;

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
            'date' => $this->faker->date('Y-m-d'),
            'hour_start' => $this->faker->date('H:i:s'),
            'hour_end' => $this->faker->date('H:i:s'),
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
