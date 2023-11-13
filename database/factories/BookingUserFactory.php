<?php

namespace Database\Factories;

use App\Models\BookingUser;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Client;
use App\Models\Degree;
use App\Models\Course;
use App\Models\CourseGroup;
use App\Models\Booking;
use App\Models\CourseDate;
use App\Models\Monitor;

class BookingUserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = BookingUser::class;

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
            'booking_id' => $this->faker->word,
            'client_id' => $this->faker->word,
            'price' => $this->faker->numberBetween(0, 9223372036854775807),
            'currency' => $this->faker->lexify('?????'),
            'course_subgroup_id' => $this->faker->word,
            'course_id' => $this->faker->word,
            'course_date_id' => $this->faker->word,
            'degree_id' => $this->faker->word,
            'course_group_id' => $this->faker->word,
            'monitor_id' => $this->faker->word,
            'date' => $this->faker->date('Y-m-d'),
            'hour_start' => $this->faker->date('H:i:s'),
            'hour_end' => $this->faker->date('H:i:s'),
            'attended' => $this->faker->boolean,
            'color' => $this->faker->text($this->faker->numberBetween(5, 45)),
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
