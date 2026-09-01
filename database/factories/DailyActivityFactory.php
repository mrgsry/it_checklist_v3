<?php

namespace Database\Factories;

use App\Models\DailyActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailyActivity>
 */
class DailyActivityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'assigned_by' => null,
            'assigned_at' => null,
            'activity_date' => today(),
            'activity' => fake()->sentence(5),
            'status' => 'completed',
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
