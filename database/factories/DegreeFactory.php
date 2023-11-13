<?php

namespace Database\Factories;

use App\Models\Degree;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\School;
use App\Models\Sport;

class DegreeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Degree::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        
        $sport = Sport::first();
        if (!$sport) {
            $sport = Sport::factory()->create();
        }

        return [
            'league' => $this->faker->text($this->faker->numberBetween(5, 255)),
            'level' => $this->faker->text($this->faker->numberBetween(5, 255)),
            'name' => $this->faker->text($this->faker->numberBetween(5, 100)),
            'annotation' => $this->faker->text($this->faker->numberBetween(5, 65535)),
            'degree_order' => $this->faker->word,
            'progress' => $this->faker->word,
            'color' => $this->faker->text($this->faker->numberBetween(5, 10)),
            'school_id' => $this->faker->word,
            'sport_id' => $this->faker->word,
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
