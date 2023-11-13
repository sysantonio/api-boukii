<?php

namespace Database\Factories;

use App\Models\EmailLog;
use Illuminate\Database\Eloquent\Factories\Factory;


class EmailLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EmailLog::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        
        return [
            'date' => $this->faker->date('Y-m-d H:i:s'),
            'from' => $this->faker->text($this->faker->numberBetween(5, 255)),
            'to' => $this->faker->text($this->faker->numberBetween(5, 255)),
            'cc' => $this->faker->text($this->faker->numberBetween(5, 255)),
            'bcc' => $this->faker->text($this->faker->numberBetween(5, 10000)),
            'subject' => $this->faker->text($this->faker->numberBetween(5, 255)),
            'body' => $this->faker->text($this->faker->numberBetween(5, 65535)),
            'headers' => $this->faker->text($this->faker->numberBetween(5, 65535)),
            'attachments' => $this->faker->text($this->faker->numberBetween(5, 4096)),
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
