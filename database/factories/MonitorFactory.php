<?php

namespace Database\Factories;

use App\Models\Monitor;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Language;
use App\Models\School;


use App\Models\User;

class MonitorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Monitor::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {


        $user = User::factory()->create();
        $languages = \App\Models\Language::inRandomOrder()->take(3)->get();

        return [
            'email' => $this->faker->unique()->safeEmail,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'birth_date' => $this->faker->date('Y-m-d'),
            'phone' => $this->faker->numerify('0##########'),
            'telephone' => $this->faker->numerify('0##########'),
            'address' => $this->faker->address,
            'cp' => $this->faker->postcode,
            'city' => $this->faker->city,
            'province' => $this->faker->state,
            'country' => $this->faker->country,
            'language1_id' => $languages[0]->id,
            'language2_id' => $languages[1]->id,
            'language3_id' => $languages[2]->id,
            'image' => $this->faker->imageUrl(),
            'avs' => $this->faker->swiftBicNumber,
            'work_license' => $this->faker->randomElement(['Yes', 'No']),
            'bank_details' => $this->faker->bankAccountNumber,
            'children' => $this->faker->boolean,
            'civil_status' => $this->faker->randomElement(['single', 'married', 'divorced', 'widowed']),
            'family_allowance' => $this->faker->boolean,
            'partner_work_license' => $this->faker->randomElement(['Yes', 'No']),
            'partner_works' => $this->faker->boolean,
            'partner_percentage' => $this->faker->numberBetween(0, 100),
            'user_id' => User::inRandomOrder()->first()->id,
            'active_school' => School::inRandomOrder()->first()->id,
            'created_at' => $this->faker->dateTimeThisDecade(),
            'updated_at' => $this->faker->dateTimeThisYear(),
            'deleted_at' => null
        ];
    }
}
