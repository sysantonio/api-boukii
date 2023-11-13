<?php

namespace Database\Factories;

use App\Models\CourseSubgroup;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Degree;
use App\Models\Course;
use App\Models\CourseGroup;
use App\Models\CourseDate;
use App\Models\Monitor;

class CourseSubgroupFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CourseSubgroup::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        
        $monitor = Monitor::first();
        if (!$monitor) {
            $monitor = Monitor::factory()->create();
        }

        return [
            'course_id' => $this->faker->word,
            'course_date_id' => $this->faker->word,
            'degree_id' => $this->faker->word,
            'course_group_id' => $this->faker->word,
            'monitor_id' => $this->faker->word,
            'max_participants' => $this->faker->word,
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
