<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Webinar>
 */
class WebinarFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'event_date' => fake()->dateTimeBetween('+1 week', '+2 months')->format('Y-m-d'),
            'start_time' => fake()->randomElement(['14:00', '19:00', '20:00']),
            'end_time' => null,
            'timezone' => 'WIB',
            'platform' => fake()->randomElement(['Zoom', 'Google Meet']),
        ];
    }
}
