<?php

namespace Database\Factories;

use App\Models\Sport;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\SportType;

class SportFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Sport::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $sportType = SportType::first();
        if (!$sportType) {
            $sportType = SportType::factory()->create();
        }

        return [
            'name' => $this->faker->text($this->faker->numberBetween(5, 255)),
            'icon_selected' => $this->faker->text($this->faker->numberBetween(5, 500)),
            'icon_unselected' => $this->faker->text($this->faker->numberBetween(5, 500)),
            'sport_type' => $this->faker->word,
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
