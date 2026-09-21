<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mentoring>
 */
class MentoringFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'session_duration_minutes' => fake()->randomElement([30, 60, 90]),
            'number_of_sessions' => fake()->numberBetween(1, 4),
            'session_type' => fake()->randomElement(['1_on_1', 'group']),
            'delivery_mode' => 'online',
        ];
    }
}
