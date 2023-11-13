<?php

namespace Database\Factories;

use App\Models\BookingUserExtra;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\BookingUser;
use App\Models\CourseExtra;

class BookingUserExtraFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = BookingUserExtra::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        
        $courseExtra = CourseExtra::first();
        if (!$courseExtra) {
            $courseExtra = CourseExtra::factory()->create();
        }

        return [
            'boouking_user_id' => $this->faker->word,
            'course_extra_id' => $this->faker->word,
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
