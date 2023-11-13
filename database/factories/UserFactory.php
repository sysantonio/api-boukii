<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;


class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        
        return [
            'username' => $this->faker->text($this->faker->numberBetween(5, 255)),
            'email' => $this->faker->email,
            'password' => $this->faker->lexify('1???@???A???'),
            'image' => $this->faker->text($this->faker->numberBetween(5, 255)),
            'type' => $this->faker->text($this->faker->numberBetween(5, 100)),
            'active' => $this->faker->boolean,
            'recover_token' => $this->faker->text($this->faker->numberBetween(5, 65535)),
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => $this->faker->word,
            'logout' => $this->faker->boolean
        ];
    }
}
