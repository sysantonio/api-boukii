<?php

namespace Database\Factories;

use App\Models\StationService;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Station;
use App\Models\ServiceType;

class StationServiceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StationService::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        
        $serviceType = ServiceType::first();
        if (!$serviceType) {
            $serviceType = ServiceType::factory()->create();
        }

        return [
            'station_id' => $this->faker->word,
            'service_type_id' => $this->faker->word,
            'name' => $this->faker->text($this->faker->numberBetween(5, 100)),
            'url' => $this->faker->text($this->faker->numberBetween(5, 100)),
            'telephone' => $this->faker->numerify('0##########'),
            'email' => $this->faker->email,
            'image' => $this->faker->text($this->faker->numberBetween(5, 255)),
            'active' => $this->faker->boolean,
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
