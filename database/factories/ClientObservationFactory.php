<?php

namespace Database\Factories;

use App\Models\ClientObservation;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\School;
use App\Models\Client;

class ClientObservationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ClientObservation::class;

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
            'general' => $this->faker->text($this->faker->numberBetween(5, 5000)),
            'notes' => $this->faker->text($this->faker->numberBetween(5, 5000)),
            'historical' => $this->faker->text($this->faker->numberBetween(5, 5000)),
            'client_id' => $this->faker->word,
            'school_id' => $this->faker->word,
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
