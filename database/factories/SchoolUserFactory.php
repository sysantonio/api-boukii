<?php

namespace Database\Factories;

use App\Models\SchoolUser;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\User;
use App\Models\School;

class SchoolUserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SchoolUser::class;

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
            'school_id' => $this->faker->word,
            'user_id' => $this->faker->word,
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
