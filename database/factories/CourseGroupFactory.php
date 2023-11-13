<?php

namespace Database\Factories;

use App\Models\CourseGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Degree;
use App\Models\CourseDate;
use App\Models\Degree;
use App\Models\Course;

class CourseGroupFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CourseGroup::class;

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
            'course_date_id' => $this->faker->word,
            'degree_id' => $this->faker->word,
            'age_min' => $this->faker->word,
            'age_max' => $this->faker->word,
            'recommended_age' => $this->faker->word,
            'teachers_min' => $this->faker->word,
            'teachers_max' => $this->faker->word,
            'observations' => $this->faker->text($this->faker->numberBetween(5, 65535)),
            'teacher_min_degree' => $this->faker->word,
            'auto' => $this->faker->boolean,
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
