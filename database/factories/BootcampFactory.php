<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bootcamp>
 */
class BootcampFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'duration' => fake()->randomElement(['8 minggu', '12 minggu', '16 minggu']),
            'schedule' => 'Senin & Rabu, 19:00-21:00 WIB',
            'cohort' => 'Batch '.fake()->numberBetween(1, 10),
            'delivery_mode' => fake()->randomElement(['online', 'offline', 'hybrid']),
        ];
    }
}
