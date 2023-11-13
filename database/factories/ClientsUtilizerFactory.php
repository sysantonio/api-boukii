<?php

namespace Database\Factories;

use App\Models\ClientsUtilizer;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Client;
use App\Models\Client;

class ClientsUtilizerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ClientsUtilizer::class;

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
            'main_id' => $this->faker->word,
            'client_id' => $this->faker->word,
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
