<?php

namespace Database\Factories;

use App\Models\Mail;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\School;

class MailFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Mail::class;

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
            'type' => $this->faker->text($this->faker->numberBetween(5, 125)),
            'subject' => $this->faker->text($this->faker->numberBetween(5, 125)),
            'body' => $this->faker->text($this->faker->numberBetween(5, 65535)),
            'school_id' => $this->faker->word,
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
