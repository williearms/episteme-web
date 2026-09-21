<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Workshop>
 */
class WorkshopFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'event_date' => fake()->dateTimeBetween('+1 week', '+2 months')->format('Y-m-d'),
            'duration_minutes' => fake()->randomElement([180, 240, 360]),
            'mode' => fake()->randomElement(['online', 'offline', 'hybrid']),
            'location' => fake()->optional()->city(),
        ];
    }
}
