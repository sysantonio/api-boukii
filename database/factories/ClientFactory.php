<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Language;
use App\Models\User;


class ClientFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Client::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        $language = Language::first();
        if (!$language) {
            $language = Language::factory()->create();
        }

        return [
            'email' => $this->faker->email,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'birth_date' => $this->faker->date('Y-m-d'),
            'phone' => $this->faker->numerify('0##########'),
            'telephone' => $this->faker->numerify('0##########'),
            'address' => $this->faker->text($this->faker->numberBetween(5, 255)),
            'cp' => $this->faker->text($this->faker->numberBetween(5, 100)),
            'city' => $this->faker->text($this->faker->numberBetween(5, 65535)),
            'province' => $this->faker->text($this->faker->numberBetween(5, 100)),
            'country' => $this->faker->text($this->faker->numberBetween(5, 100)),
            'language1_id' => $this->faker->word,
            'language2_id' => $this->faker->word,
            'language3_id' => $this->faker->word,
            'image' => $this->faker->text($this->faker->numberBetween(5, 255)),
            'user_id' => $this->faker->word,
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => $this->faker->word
        ];
    }
}
