<?php

namespace Database\Factories;

use App\Models\VouchersLog;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Booking;
use App\Models\Voucher;

class VouchersLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = VouchersLog::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        
        $voucher = Voucher::first();
        if (!$voucher) {
            $voucher = Voucher::factory()->create();
        }

        return [
            'voucher_id' => $this->faker->word,
            'booking_id' => $this->faker->word,
            'amount' => $this->faker->numberBetween(0, 9223372036854775807),
            'created_at' => $this->faker->date('Y-m-d H:i:s'),
            'updated_at' => $this->faker->date('Y-m-d H:i:s'),
            'deleted_at' => $this->faker->date('Y-m-d H:i:s')
        ];
    }
}
