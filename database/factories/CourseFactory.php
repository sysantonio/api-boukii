<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Sport;
use App\Models\School;
use App\Models\Station;

class CourseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Course::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        
        $station = Station::first();
        if (!$station) {
            $station = Station::factory()->create();
        }

        return [
            'course_type' => $this->faker->boolean,
            'is_flexible' => $this->faker->boolean,
            'sport_id' => $this->faker->word,
            'school_id' => $this->faker->word,
            'station_id' => $this->faker->word,
            'name' => $this->faker->text($this->faker->numberBetween(5, 65535)),
            'short_description' => $this->faker->text($this->faker->numberBetween(5, 65535)),
            'description' => $this->faker->text($this->faker->numberBetween(5, 65535)),
            'price' => $this->faker->numberBetween(0, 9223372036854775807),
            'currency' => $this->faker->lexify('?????'),
            'max_participants' => $this->faker->word,
            'duration' => $this->faker->date('H:i:s'),
            'duration_flexible' => $this->faker->boolean,
            'date_start' => $this->faker->date('Y-m-d'),
            'date_end' => $this->faker->date('Y-m-d'),
            'date_start_res' => $this->faker->date('Y-m-d'),
            'date_end_res' => $this->faker->date('Y-m-d'),
            'hour_min' => $this->faker->text($this->faker->numberBetween(5, 255)),
            'hour_max' => $this->faker->text($this->faker->numberBetween(5, 255)),
            'confirm_attendance' => $this->faker->boolean,
            'active' => $this->faker->boolean,
            'online' => $this->faker->boolean,
            'image' => $this->faker->text($this->faker->numberBetween(5, 4096)),
            'translations' => $this->faker->text($this->faker->numberBetween(5, 4096)),
            'price_range' => $this->faker->text($this->faker->numberBetween(5, 4096)),
            'discounts' => $this->faker->text($this->faker->numberBetween(5, 4096)),
            'settings' => $this->faker->text($this->faker->numberBetween(5, 4096)),
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
