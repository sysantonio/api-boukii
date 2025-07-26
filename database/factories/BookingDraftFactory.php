<?php

namespace Database\Factories;

use App\Models\BookingDraft;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingDraftFactory extends Factory
{
    protected $model = BookingDraft::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'session_id' => $this->faker->uuid,
            'data' => ['currentStep' => 1],
            'expires_at' => now()->addDay(),
        ];
    }
}
