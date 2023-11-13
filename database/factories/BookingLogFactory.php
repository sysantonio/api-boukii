<?php

namespace Database\Factories;

use App\Models\BookingLog;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Booking;
use App\Models\User;

class BookingLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = BookingLog::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        
        $user = User::first();
        if (!$user) {
            $user = User::factory()->create();
        }

        return [
            'booking_id' => $this->faker->word,
            'action' => $this->faker->text($this->faker->numberBetween(5, 100)),
            'description' => $this->faker->text($this->faker->numberBetween(5, 65535)),
            'user_id' => $this->faker->word,
            'before_change' => $this->faker->text($this->faker->numberBetween(5, 4096)),
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
