<?php

namespace Database\Factories;

use App\Models\ClientsSchool;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\School;
use App\Models\Client;

class ClientsSchoolFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ClientsSchool::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        
        $client = Client::first();
        if (!$client) {
            $client = Client::factory()->create();
        }

        return [
            'client_id' => $this->faker->word,
            'school_id' => $this->faker->word,
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => $this->faker->date('Y-m-d H:i:s'),
            'status_updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'accepted_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
