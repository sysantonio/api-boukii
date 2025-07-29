<?php

namespace Database\Factories;

use App\V5\Models\UserSeasonRole;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserSeasonRoleFactory extends Factory
{
    protected $model = UserSeasonRole::class;

    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'season_id' => \App\V5\Models\Season::factory(),
            'role' => $this->faker->randomElement(['admin', 'manager', 'viewer']),
        ];
    }
}

