<?php

namespace Database\Factories;

use App\Models\ChecklistForm;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChecklistForm>
 */
class ChecklistFormFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'schedule_type' => 'daily',
            'schedule_days' => [],
            'schedule_interval' => null,
            'start_date' => now()->toDateString(),
            'end_date' => null,
            'is_active' => true,
            'created_by' => User::factory(),
        ];
    }
}
