<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'duration_minutes' => fake()->randomElement([120, 180, 240, 360, 480]),
            'total_modules' => fake()->numberBetween(4, 14),
            'total_lessons' => fake()->numberBetween(12, 60),
            'delivery_type' => fake()->randomElement(['self_paced', 'cohort_based']),
        ];
    }
}
