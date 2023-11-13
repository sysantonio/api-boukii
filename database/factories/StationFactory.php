<?php

namespace Database\Factories;

use App\Models\Station;
use Illuminate\Database\Eloquent\Factories\Factory;


class StationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Station::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        
        return [
            'name' => $this->faker->text($this->faker->numberBetween(5, 255)),
            'cp' => $this->faker->text($this->faker->numberBetween(5, 65535)),
            'city' => $this->faker->text($this->faker->numberBetween(5, 65535)),
            'country' => $this->faker->text($this->faker->numberBetween(5, 65535)),
            'province' => $this->faker->text($this->faker->numberBetween(5, 65535)),
            'address' => $this->faker->text($this->faker->numberBetween(5, 100)),
            'image' => $this->faker->text($this->faker->numberBetween(5, 500)),
            'map' => $this->faker->text($this->faker->numberBetween(5, 500)),
            'latitude' => $this->faker->text($this->faker->numberBetween(5, 100)),
            'longitude' => $this->faker->text($this->faker->numberBetween(5, 100)),
            'num_hanger' => $this->faker->word,
            'num_chairlift' => $this->faker->word,
            'num_cabin' => $this->faker->word,
            'num_cabin_large' => $this->faker->word,
            'num_fonicular' => $this->faker->word,
            'show_details' => $this->faker->boolean,
            'active' => $this->faker->boolean,
            'accuweather' => $this->faker->text($this->faker->numberBetween(5, 65535)),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
