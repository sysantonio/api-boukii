<?php

namespace Database\Factories;

use App\Models\DiscountCode;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\School;

class DiscountCodeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DiscountCode::class;

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
            'code' => $this->faker->text($this->faker->numberBetween(5, 255)),
            'quantity' => $this->faker->numberBetween(0, 9223372036854775807),
            'percentage' => $this->faker->numberBetween(0, 9223372036854775807),
            'school_id' => $this->faker->word,
            'total' => $this->faker->word,
            'remaining' => $this->faker->word,
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
